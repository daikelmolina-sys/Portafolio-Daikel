<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCartRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

/**
 * CartController — Valida el stock del carrito en tiempo real.
 *
 * Endpoints:
 *  POST /api/cart/validate — Verifica si todos los items tienen stock disponible.
 *  GET  /api/cart/stock/{productId} — Stock disponible de un producto específico.
 */
class CartController extends Controller
{
    public function __construct(private readonly ProductRepository $productRepo) {}

    /**
     * Valida el stock para todos los items del carrito.
     * Usado por el Optimistic UI para revertir si hay escasez.
     *
     * @return JsonResponse {
     *   valid: bool,
     *   items: [{ product_id, requested, available, valid, message? }]
     * }
     */
    public function validate(ValidateCartRequest $request): JsonResponse
    {
        $results    = [];
        $allValid   = true;

        foreach ($request->input('items') as $item) {
            $product   = Product::with(['recipes.ingredient'])->find($item['product_id']);
            $requested = $item['quantity'];
            $available = $this->productRepo->calculateAvailableStock($product);
            $valid     = $available >= $requested;

            if (! $valid) {
                $allValid = false;
            }

            $results[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'requested'  => $requested,
                'available'  => $available,
                'valid'      => $valid,
                'message'    => $valid
                    ? null
                    : "Solo quedan {$available} unidades de '{$product->name}'.",
            ];
        }

        return response()->json([
            'valid' => $allValid,
            'items' => $results,
        ], $allValid ? 200 : 422);
    }

    /**
     * Retorna el stock disponible de un producto individual.
     * Usado para actualizar el badge de stock en tiempo real en el frontend.
     */
    public function stockForProduct(int $productId): JsonResponse
    {
        $product = Product::with(['recipes.ingredient'])->findOrFail($productId);
        $stock   = $this->productRepo->calculateAvailableStock($product);

        return response()->json([
            'product_id'   => $productId,
            'product_name' => $product->name,
            'available'    => $stock,
            'is_available' => $stock > 0,
        ]);
    }
}
