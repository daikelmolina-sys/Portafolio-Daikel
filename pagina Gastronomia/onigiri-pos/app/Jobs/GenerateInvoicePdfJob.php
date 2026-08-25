<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

/**
 * GenerateInvoicePdfJob — Genera la factura en PDF de forma ASÍNCRONA.
 *
 * Usa el queue worker de Redis (cola "pdf") para no bloquear la respuesta HTTP.
 * Al terminar, actualiza el estado del Invoice con la ruta del archivo generado.
 *
 * Tecnología: Utiliza DomPDF vía Blade template.
 * Instalar: composer require barryvdh/laravel-dompdf
 */
class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(private readonly Invoice $invoice) {}

    public function handle(): void
    {
        // Marcar como procesando
        $this->invoice->update(['pdf_status' => 'processing']);

        try {
            // Cargar la orden con todos sus datos para la factura
            $invoice = $this->invoice->load(['order.items.product', 'order.user']);
            $order   = $invoice->order;

            // ----------------------------------------------------------------
            // Generar HTML desde Blade template
            // ----------------------------------------------------------------
            $html = View::make('pdf.invoice', compact('invoice', 'order'))->render();

            // ----------------------------------------------------------------
            // Convertir a PDF con DomPDF (si está instalado)
            // Fallback: guardar HTML si DomPDF no está disponible en dev
            // ----------------------------------------------------------------
            $filename = "invoices/{$invoice->invoice_number}.pdf";

            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                Storage::disk('local')->put($filename, $pdf->output());
            } else {
                // Fallback para desarrollo sin DomPDF
                Storage::disk('local')->put(
                    str_replace('.pdf', '.html', $filename),
                    $html
                );
                $filename = str_replace('.pdf', '.html', $filename);
            }

            // Actualizar la factura con la ruta del PDF
            $this->invoice->update([
                'pdf_status' => 'generated',
                'pdf_path'   => $filename,
            ]);

        } catch (\Throwable $e) {
            $this->invoice->update(['pdf_status' => 'failed']);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->invoice->update(['pdf_status' => 'failed']);
    }
}
