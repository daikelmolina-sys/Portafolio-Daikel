<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Movimientos de Inventario — Log auditado de cada cambio de stock.
 *
 * NORMALIZACIÓN 3FN:
 * - Tabla separada de ingredients para no mutar el dato en caliente.
 * - Cada transacción de stock (entrada o salida) genera una fila aquí.
 * - Esto permite: historial completo, rollback de operaciones y analytics de consumo.
 *
 * TIPOS DE MOVIMIENTO:
 *  - purchase:  Compra/reposición de ingrediente (entrada +)
 *  - sale:      Descuento por venta confirmada (salida -)
 *  - adjustment: Ajuste manual del cajero/admin (+ o -)
 *  - waste:     Merma/desperdicio (salida -)
 *  - return:    Devolución de una venta (entrada +)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_movements', function (Blueprint $table) {
            $table->id();

            // FK al ingrediente afectado
            $table->foreignId('ingredient_id')
                  ->constrained('ingredients')
                  ->restrictOnDelete();

            // FK al pedido que originó el movimiento (nullable para ajustes manuales)
            $table->foreignId('order_id')
                  ->nullable()
                  ->constrained('orders')
                  ->nullOnDelete();

            // FK al usuario que registró el movimiento
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Tipo de movimiento
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'waste', 'return']);

            // Cantidad del movimiento (POSITIVO = entrada, NEGATIVO = salida)
            $table->decimal('quantity', 10, 3);

            // Stock del ingrediente ANTES del movimiento (para auditoría)
            $table->decimal('stock_before', 10, 3);

            // Stock del ingrediente DESPUÉS del movimiento
            $table->decimal('stock_after', 10, 3);

            // Nota explicativa del movimiento (obligatoria en ajustes manuales)
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            // Índices para reportes de inventario
            $table->index(['ingredient_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_movements');
    }
};
