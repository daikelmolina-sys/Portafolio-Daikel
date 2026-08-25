<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

/**
 * CatalogController — Expone el catálogo de productos a la SPA de React.
 */
class CatalogController extends Controller
{
    public function __construct(private readonly ProductRepository $productRepo) {}

    /**
     * GET /api/catalog — Catálogo completo para la tienda.
     */
    public function index(): JsonResponse
    {
        $products = $this->productRepo->getAvailableCatalog();

        return response()->json([
            'data' => $products->map(fn($p) => $this->formatProduct($p)),
        ]);
    }

    /**
     * GET /api/catalog/featured — Productos destacados para el hero.
     */
    public function featured(): JsonResponse
    {
        $products = $this->productRepo->getFeatured(4);

        return response()->json([
            'data' => $products->map(fn($p) => $this->formatProduct($p)),
        ]);
    }

    /**
     * GET /api/catalog/{id}/stock — Stock calculado de un producto.
     */
    public function stock(int $id): JsonResponse
    {
        $product = $this->productRepo->findWithRecipe($id);

        if (! $product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        return response()->json([
            'product_id' => $id,
            'stock'      => $this->productRepo->calculateAvailableStock($product),
        ]);
    }

    private function formatProduct($product): array
    {
        return [
            'id'                => $product->id,
            'name'              => $product->name,
            'slug'              => $product->slug,
            'short_description' => $product->short_description,
            'long_description'  => $product->long_description,
            'price'             => (float) $product->price,
            'compare_price'     => $product->compare_price ? (float) $product->compare_price : null,
            'discount_pct'      => $product->discountPercentage(),
            'main_image'        => $product->main_image_path,
            'tags'              => $product->tags ?? [],
            'prep_time'         => $product->prep_time_minutes,
            'category'          => [
                'id'   => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'is_featured'       => $product->is_featured,
        ];
    }
}
