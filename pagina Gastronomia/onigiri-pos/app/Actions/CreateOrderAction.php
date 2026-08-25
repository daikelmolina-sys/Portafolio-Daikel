<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * CreateOrderAction — Crea la cabecera y los items de un pedido.
 *
 * Se separa de ConfirmOrderAction para permitir pedidos en estado "pending"
 * (carrito online) antes de ser confirmados (stock descontado).
 */
class CreateOrderAction
{
    /**
     * @param array $orderData   Datos de cabecera (cliente, canal, entrega, pago)
     * @param array $cartItems   [['product_id' => x, 'quantity' => y, 'notes' => z], ...]
     */
    public function execute(array $orderData, array $cartItems): Order
    {
        return DB::transaction(function () use ($orderData, $cartItems) {

            // 1. Calcular totales desde los productos reales (no confiar en el frontend)
            $totals = $this->calculateTotals($cartItems, $orderData['tax_rate'] ?? 18.00);

            // 2. Crear la orden cabecera
            $order = Order::create(array_merge($orderData, [
                'subtotal'   => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'total'      => $totals['total'],
                'status'     => 'pending',
            ]));

            // 3. Crear los items
            foreach ($cartItems as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id'             => $order->id,
                    'product_id'           => $product->id,
                    'product_name'         => $product->name,
                    'product_sku'          => $product->slug,
                    'quantity'             => $item['quantity'],
                    'unit_price'           => $product->price,
                    'discount_per_unit'    => 0,
                    'line_total'           => $product->price * $item['quantity'],
                    'customization_notes'  => $item['notes'] ?? null,
                    'item_status'          => 'pending',
                ]);
            }

            return $order->load('items');
        });
    }

    private function calculateTotals(array $cartItems, float $taxRate): array
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $product   = Product::findOrFail($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }

        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $total     = $subtotal + $taxAmount;

        return compact('subtotal', 'taxAmount', 'total');
    }
}
