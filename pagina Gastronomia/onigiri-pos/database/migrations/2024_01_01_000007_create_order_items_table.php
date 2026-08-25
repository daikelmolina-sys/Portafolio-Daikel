<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detalles del Pedido (Order Items) — Líneas de producto de cada orden.
 *
 * NORMALIZACIÓN 3FN:
 * - Relación 1:N con orders (un pedido tiene muchos items).
 * - SNAPSHOT del producto al momento de la compra: precio, nombre.
 *   Esto garantiza que aunque el precio del producto cambie, el historial queda intacto.
 * - Los ingredientes consumidos quedan registrados en `ingredient_movements`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // FK a la orden cabecera
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();

            // FK al producto (nullable: si el producto se elimina, se conserva el item)
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();

            // ---------------------------------------------------------------
            // SNAPSHOT del producto (preserva historial)
            // ---------------------------------------------------------------
            $table->string('product_name', 200);         // Nombre al momento de comprar
            $table->string('product_sku', 50)->nullable(); // SKU del producto

            // ---------------------------------------------------------------
            // CANTIDAD Y PRECIOS
            // ---------------------------------------------------------------
            $table->unsignedSmallInteger('quantity');             // Unidades pedidas

            $table->decimal('unit_price', 8, 2);                 // Precio unitario al comprar
            $table->decimal('discount_per_unit', 8, 2)->default(0); // Descuento unitario

            // Precio neto = (unit_price - discount_per_unit) * quantity
            $table->decimal('line_total', 10, 2);

            // ---------------------------------------------------------------
            // PERSONALIZACIÓN / NOTAS DEL ITEM
            // ---------------------------------------------------------------
            // Variaciones o instrucciones especiales (ej: "sin alga", "extra salmón")
            $table->string('customization_notes', 500)->nullable();

            // Estado de preparación del item (independiente del estado de la orden)
            $table->enum('item_status', [
                'pending',
                'preparing',
                'ready',
            ])->default('pending');

            $table->timestamps();

            // Índice para cargar items de una orden rápidamente
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
