<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ingredientes individuales del inventario (Arroz, Nori, Salmón, etc.)
 *
 * NORMALIZACIÓN 3FN:
 * - Cada ingrediente es un concepto atómico e independiente.
 * - La relación ingrediente↔producto se gestiona en la tabla "recipes".
 * - El stock se controla aquí; al vender, se descuenta transaccionalmente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();

            // Nombre del ingrediente (ej: "Salmón fresco", "Arroz Koshihikari")
            $table->string('name', 150);

            // SKU / código interno para control de inventario
            $table->string('sku', 50)->unique();

            // Descripción adicional (proveedor, notas de calidad, etc.)
            $table->string('description', 255)->nullable();

            // ---------------------------------------------------------------
            // STOCK — Unidades disponibles actualmente
            // ---------------------------------------------------------------
            // Tipo DECIMAL para soportar fracciones (ej: 0.5 kg de salmón)
            $table->decimal('stock_quantity', 10, 3)->default(0);

            // Unidad de medida (gr, kg, unidad, ml, lt)
            $table->enum('unit', ['gr', 'kg', 'ml', 'lt', 'unit'])->default('gr');

            // ---------------------------------------------------------------
            // ALERTAS DE STOCK
            // ---------------------------------------------------------------
            // Cantidad mínima antes de lanzar alerta de reabastecimiento
            $table->decimal('min_stock_alert', 10, 3)->default(0);

            // Costo unitario para calcular margen (opcional)
            $table->decimal('unit_cost', 8, 2)->default(0);

            // Estado activo (permite desactivar ingrediente sin borrar historial)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Índice para búsquedas rápidas por nombre
            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
