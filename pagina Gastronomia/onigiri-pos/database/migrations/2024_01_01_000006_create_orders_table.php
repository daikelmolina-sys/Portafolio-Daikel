<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pedidos (Orders) — Cabecera del pedido.
 *
 * NORMALIZACIÓN 3FN:
 * - La orden almacena solo datos propios: cliente, estado, canal, totales, dirección de entrega.
 * - Los PRODUCTOS del pedido se almacenan en `order_items` (relación 1:N).
 * - Los datos del cliente (nombre, email) se desnormalizan intencionalmente como snapshot
 *   para preservar el historial aunque el usuario modifique su perfil posteriormente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Número de pedido legible (ej: "ONI-2024-00042")
            $table->string('order_number', 30)->unique();

            // FK al usuario cliente (nullable: permite pedidos de invitados)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // FK al cajero que procesó el pedido (POS, nullable si es online)
            $table->foreignId('cashier_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ---------------------------------------------------------------
            // SNAPSHOT del cliente (preserva historial)
            // ---------------------------------------------------------------
            $table->string('customer_name', 150);
            $table->string('customer_email', 150)->nullable();
            $table->string('customer_phone', 20)->nullable();

            // ---------------------------------------------------------------
            // ESTADO DEL PEDIDO (máquina de estados)
            // pending → confirmed → preparing → ready → delivered | cancelled
            // ---------------------------------------------------------------
            $table->enum('status', [
                'pending',      // Creado, aún no confirmado
                'confirmed',    // Stock validado y reservado
                'preparing',    // En cocina
                'ready',        // Listo para entrega/retiro
                'delivered',    // Entregado al cliente
                'cancelled',    // Cancelado (stock liberado)
            ])->default('pending');

            // ---------------------------------------------------------------
            // CANAL DE VENTA
            // ---------------------------------------------------------------
            $table->enum('channel', [
                'online',   // Pedido desde la SPA de clientes
                'pos',      // Pedido desde el módulo de cajero
            ])->default('online');

            // ---------------------------------------------------------------
            // TIPO DE ENTREGA
            // ---------------------------------------------------------------
            $table->enum('delivery_type', [
                'pickup',   // Retiro en local
                'delivery', // Envío a domicilio
            ])->default('pickup');

            // Dirección de entrega (solo si delivery_type = 'delivery')
            $table->string('delivery_address')->nullable();
            $table->string('delivery_notes', 500)->nullable();

            // ---------------------------------------------------------------
            // TOTALES (calculados al confirmar la orden)
            // ---------------------------------------------------------------
            $table->decimal('subtotal', 10, 2)->default(0);         // Suma de items sin impuestos
            $table->decimal('discount_amount', 10, 2)->default(0);  // Descuento aplicado
            $table->decimal('tax_rate', 5, 2)->default(18.00);      // IGV en Perú = 18%
            $table->decimal('tax_amount', 10, 2)->default(0);       // Monto del impuesto
            $table->decimal('delivery_fee', 8, 2)->default(0);      // Costo de envío
            $table->decimal('total', 10, 2)->default(0);            // Total final a cobrar

            // ---------------------------------------------------------------
            // PAGO
            // ---------------------------------------------------------------
            $table->enum('payment_method', [
                'cash',         // Efectivo
                'card',         // Tarjeta débito/crédito
                'yape',         // Yape (app de pago peruana)
                'plin',         // Plin
                'transfer',     // Transferencia bancaria
            ])->nullable();

            $table->enum('payment_status', [
                'pending',
                'paid',
                'refunded',
            ])->default('pending');

            // Monto recibido (para calcular el vuelto en el POS)
            $table->decimal('amount_received', 10, 2)->nullable();

            // Vuelto calculado
            $table->decimal('change_amount', 10, 2)->nullable();

            // Notas adicionales del pedido
            $table->text('internal_notes')->nullable();

            // Timestamps de cambios de estado (para analytics)
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para queries del dashboard y POS
            $table->index(['status', 'channel']);
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
