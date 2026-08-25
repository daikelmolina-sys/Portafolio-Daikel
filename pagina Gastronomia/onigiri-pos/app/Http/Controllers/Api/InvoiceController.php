<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * InvoiceController — Gestiona la consulta y descarga de facturas.
 *
 * GET  /api/invoices/{id}          — Estado y datos de la factura
 * GET  /api/invoices/{id}/download — Descarga el PDF (si está listo)
 * POST /api/invoices/{id}/retry    — Reintentar generación si falló
 */
class InvoiceController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $invoice = Invoice::with('order')->findOrFail($id);

        return response()->json([
            'data' => [
                'id'             => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'type'           => $invoice->type,
                'series'         => $invoice->series,
                'pdf_status'     => $invoice->pdf_status,
                'pdf_ready'      => $invoice->isPdfReady(),
                'pdf_url'        => $invoice->isPdfReady()
                    ? route('api.invoices.download', $invoice->id)
                    : null,
                'receiver_name'  => $invoice->receiver_name,
                'total'          => (float) $invoice->total,
                'issued_at'      => $invoice->issued_at->format('d/m/Y'),
            ],
        ]);
    }

    public function download(int $id): StreamedResponse|JsonResponse
    {
        $invoice = Invoice::findOrFail($id);

        if (! $invoice->isPdfReady()) {
            return response()->json([
                'message'    => 'El PDF aún no está listo.',
                'pdf_status' => $invoice->pdf_status,
            ], 202);
        }

        return Storage::disk('local')->download(
            $invoice->pdf_path,
            $invoice->invoice_number . '.pdf'
        );
    }

    public function retry(int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->pdf_status !== 'failed') {
            return response()->json([
                'message' => 'Solo se puede reintentar facturas con estado "failed".',
            ], 422);
        }

        $invoice->update(['pdf_status' => 'pending']);
        GenerateInvoicePdfJob::dispatch($invoice)->onQueue('pdf');

        return response()->json(['message' => 'Regeneración de PDF enviada a la cola.']);
    }
}
