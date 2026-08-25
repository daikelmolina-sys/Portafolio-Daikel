<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Facturas (Invoices) — Documento fiscal generado para cada pedido.
 *
 * NORMALIZACIÓN 3FN:
 * - Relación 1:1 con orders (un pedido puede tener como máximo una factura).
 * - Almacena los datos fiscales de forma independiente: RUC, razón social, etc.
 * - El PDF se genera de forma ASÍNCRONA mediante un Laravel Job y solo se guarda la ruta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // FK a la orden (relación 1:1, unique garantiza solo 1 factura por orden)
            $table->foreignId('order_id')
                  ->unique()
                  ->constrained('orders')
                  ->restrictOnDelete();

            // Número de factura (ej: "F001-00042" — serie + correlativo)
            $table->string('invoice_number', 30)->unique();

            // Serie de la factura (ej: "F001" para facturas, "B001" para boletas)
            $table->string('series', 10)->default('B001');

            // Tipo de comprobante
            $table->enum('type', ['boleta', 'factura'])->default('boleta');

            // ---------------------------------------------------------------
            // DATOS FISCALES DEL RECEPTOR (cliente)
            // ---------------------------------------------------------------
            $table->string('receiver_name', 200);                  // Nombre/Razón Social
            $table->string('receiver_document_type', 10)->default('DNI'); // DNI, RUC, CE
            $table->string('receiver_document_number', 20)->nullable();
            $table->string('receiver_address')->nullable();

            // ---------------------------------------------------------------
            // MONTOS (copia de la orden para integridad del documento)
            // ---------------------------------------------------------------
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('total', 10, 2);

            // ---------------------------------------------------------------
            // PDF GENERADO ASINCRÓNICAMENTE
            // ---------------------------------------------------------------
            $table->enum('pdf_status', [
                'pending',      // Job en cola, aún no generado
                'processing',   // Job ejecutándose
                'generated',    // PDF listo
                'failed',       // Error en generación
            ])->default('pending');

            // Ruta del PDF en storage (accesible vía URL firmada de Laravel)
            $table->string('pdf_path')->nullable();

            // Notas adicionales impresas en la factura
            $table->text('notes')->nullable();

            // Fecha de emisión del comprobante (puede diferir del created_at)
            $table->date('issued_at');

            $table->timestamps();

            $table->index(['type', 'series']);
            $table->index('issued_at');
            $table->index('pdf_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
