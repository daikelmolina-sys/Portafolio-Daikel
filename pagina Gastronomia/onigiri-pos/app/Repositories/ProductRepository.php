<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    private const CACHE_TTL = 3600;          // 1 hora
    private const CACHE_KEY_CATALOG = 'catalog.products';

    /**
     * Retorna todos los productos disponibles con su categoría e ingredientes.
     * Usa Redis como caché para evitar queries repetitivas al catálogo.
     */
    public function getAvailableCatalog(): Collection
    {
        return Cache::remember(self::CACHE_KEY_CATALOG, self::CACHE_TTL, function () {
            return Product::with(['category', 'ingredients.recipes'])
                ->where('is_available', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Obtiene un producto con su receta completa (ingredientes + cantidades).
     */
    public function findWithRecipe(int $id): ?Product
    {
        return Product::with(['ingredients', 'recipes.ingredient'])
            ->find($id);
    }

    /**
     * Invalida la caché del catálogo.
     * Se llama desde eventos cuando se confirma una venta.
     */
    public function invalidateCatalogCache(): void
    {
        Cache::forget(self::CACHE_KEY_CATALOG);
    }

    /**
     * Retorna productos destacados para el hero de la tienda.
     */
    public function getFeatured(int $limit = 4): Collection
    {
        return Cache::remember("catalog.featured.{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Product::with('category')
                ->where('is_available', true)
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Retorna stock calculado para un producto según sus ingredientes.
     * El stock real = mínimo de (stock_ingrediente / cantidad_en_receta).
     */
    public function calculateAvailableStock(Product $product): int
    {
        if (! $product->tracks_ingredients) {
            return $product->direct_stock;
        }

        $recipes = $product->recipes()->with('ingredient')->get();

        if ($recipes->isEmpty()) {
            return 0;
        }

        $maxPossible = $recipes->map(function ($recipe) {
            if ($recipe->quantity_per_unit <= 0) return 0;
            return (int) floor($recipe->ingredient->stock_quantity / $recipe->quantity_per_unit);
        });

        return $maxPossible->min();
    }
}
