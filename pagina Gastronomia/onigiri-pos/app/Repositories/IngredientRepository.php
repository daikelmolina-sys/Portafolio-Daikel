<?php

namespace App\Repositories;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class IngredientRepository
{
    private const CACHE_KEY_STOCK = 'inventory.stock';
    private const CACHE_TTL       = 300; // 5 minutos (stock cambia más frecuentemente)

    /**
     * Retorna todos los ingredientes activos con su stock actual.
     * Cacheado en Redis para el dashboard de inventario.
     */
    public function getAllWithStock(): Collection
    {
        return Cache::remember(self::CACHE_KEY_STOCK, self::CACHE_TTL, function () {
            return Ingredient::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Bloquea y retorna el ingrediente para operaciones transaccionales.
     * Usa lockForUpdate() para prevenir condiciones de carrera en ventas concurrentes.
     */
    public function findForUpdate(int $id): ?Ingredient
    {
        return Ingredient::where('id', $id)->lockForUpdate()->first();
    }

    /**
     * Descuenta stock de un ingrediente y registra el movimiento.
     *
     * @throws \RuntimeException si no hay suficiente stock
     */
    public function deductStock(
        Ingredient $ingredient,
        float      $quantity,
        Order      $order,
        int        $userId
    ): IngredientMovement {
        $stockBefore = $ingredient->stock_quantity;
        $stockAfter  = $stockBefore - $quantity;

        if ($stockAfter < 0) {
            throw new \RuntimeException(
                "Stock insuficiente para '{$ingredient->name}'. " .
                "Disponible: {$stockBefore}, Requerido: {$quantity}"
            );
        }

        // Actualizar stock del ingrediente
        $ingredient->stock_quantity = $stockAfter;
        $ingredient->save();

        // Registrar movimiento en el log
        $movement = IngredientMovement::create([
            'ingredient_id' => $ingredient->id,
            'order_id'      => $order->id,
            'user_id'       => $userId,
            'type'          => 'sale',
            'quantity'      => -$quantity,   // Negativo = salida
            'stock_before'  => $stockBefore,
            'stock_after'   => $stockAfter,
            'notes'         => "Venta orden #{$order->order_number}",
        ]);

        // Invalidar caché de stock
        Cache::forget(self::CACHE_KEY_STOCK);

        return $movement;
    }

    /**
     * Devuelve stock (ej: cancelación de orden).
     */
    public function returnStock(
        Ingredient $ingredient,
        float      $quantity,
        Order      $order,
        int        $userId,
        string     $reason = 'Cancelación de orden'
    ): IngredientMovement {
        $stockBefore = $ingredient->stock_quantity;
        $stockAfter  = $stockBefore + $quantity;

        $ingredient->stock_quantity = $stockAfter;
        $ingredient->save();

        Cache::forget(self::CACHE_KEY_STOCK);

        return IngredientMovement::create([
            'ingredient_id' => $ingredient->id,
            'order_id'      => $order->id,
            'user_id'       => $userId,
            'type'          => 'return',
            'quantity'      => +$quantity,
            'stock_before'  => $stockBefore,
            'stock_after'   => $stockAfter,
            'notes'         => $reason,
        ]);
    }

    /**
     * Obtiene ingredientes con stock bajo la alerta mínima.
     */
    public function getLowStockIngredients(): Collection
    {
        return Ingredient::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->get();
    }
}
