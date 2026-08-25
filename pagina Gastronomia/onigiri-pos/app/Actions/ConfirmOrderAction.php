<?php

namespace App\Actions;

use App\Events\OrderConfirmedEvent;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\IngredientRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

/**
 * ConfirmOrderAction — Acción central del negocio.
 *
 * Responsabilidades:
 *  1. Validar que todos los ingredientes tienen stock suficiente.
 *  2. Envolver la operación en DB::transaction() para garantizar atomicidad.
 *  3. Descontar los sub-ingredientes de cada producto pedido.
 *  4. Registrar el movimiento en ingredient_movements.
 *  5. Cambiar el estado de la orden a 'confirmed'.
 *  6. Disparar el evento OrderConfirmedEvent para invalidar caché Redis.
 *
 * Si cualquier ingrediente no tiene stock suficiente → ROLLBACK completo.
 */
class ConfirmOrderAction
{
    public function __construct(
        private readonly IngredientRepository $ingredientRepo,
        private readonly OrderRepository      $orderRepo,
    ) {}

    /**
     * @throws \RuntimeException si el stock es insuficiente para algún ingrediente
     * @throws \Throwable        si la transacción falla
     */
    public function execute(Order $order, int $cashierId): Order
    {
        return DB::transaction(function () use ($order, $cashierId) {

            // 1. Cargar los items con sus recetas e ingredientes
            $order->load(['items.product.recipes.ingredient']);

            // 2. Calcular consumo total por ingrediente en todo el pedido
            //    Agrupamos antes de hacer queries para minimizar lockForUpdate
            $ingredientConsumption = $this->calculateTotalConsumption($order);

            // 3. Validar y descontar stock de cada ingrediente
            foreach ($ingredientConsumption as $ingredientId => $totalQty) {
                $ingredient = $this->ingredientRepo->findForUpdate($ingredientId);

                if (! $ingredient) {
                    throw new \RuntimeException("Ingrediente ID {$ingredientId} no encontrado.");
                }

                // Lanza RuntimeException si no hay stock → rollback automático
                $this->ingredientRepo->deductStock($ingredient, $totalQty, $order, $cashierId);
            }

            // 4. Cambiar estado de la orden
            $confirmedOrder = $this->orderRepo->updateStatus($order, 'confirmed');

            // 5. Disparar evento para invalidar caché de productos en Redis
            event(new OrderConfirmedEvent($confirmedOrder));

            return $confirmedOrder;
        });
    }

    /**
     * Agrupa el consumo total de ingredientes para todo el pedido.
     * Suma las cantidades de todos los items (respetando su quantity).
     *
     * @return array<int, float> [ingredient_id => total_quantity_to_deduct]
     */
    private function calculateTotalConsumption(Order $order): array
    {
        $consumption = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            // Bebidas o productos sin control de ingredientes: saltar
            if (! $product || ! $product->tracks_ingredients) {
                continue;
            }

            foreach ($product->recipes as $recipe) {
                $ingredientId = $recipe->ingredient_id;
                $qtyNeeded    = $recipe->quantity_per_unit * $item->quantity;

                $consumption[$ingredientId] = ($consumption[$ingredientId] ?? 0) + $qtyNeeded;
            }
        }

        return $consumption;
    }
}
