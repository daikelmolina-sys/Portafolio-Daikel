<?php

namespace App\Http\Controllers\Api;

use App\Actions\ConfirmOrderAction;
use App\Actions\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OrderController — Gestiona el ciclo de vida completo de los pedidos.
 *
 * POST /api/orders              — Crear pedido (estado: pending)
 * POST /api/orders/{id}/confirm — Confirmar + descontar stock
 * GET  /api/orders              — Listar (POS: activos, Admin: paginado)
 * GET  /api/orders/{id}         — Detalle de un pedido
 * PATCH /api/orders/{id}/status — Cambiar estado (preparing, ready, delivered)
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderAction  $createAction,
        private readonly ConfirmOrderAction $confirmAction,
        private readonly OrderRepository    $orderRepo,
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $orderData = $request->only([
            'customer_name', 'customer_email', 'customer_phone',
            'channel', 'delivery_type', 'delivery_address', 'delivery_notes',
            'payment_method', 'amount_received',
        ]);

        $orderData['user_id']  = Auth::id();
        $orderData['tax_rate'] = 18.00;

        $order = $this->createAction->execute($orderData, $request->input('items'));

        return response()->json([
            'message' => 'Pedido creado correctamente.',
            'data'    => $order->load('items'),
        ], 201);
    }

    public function confirm(int $id): JsonResponse
    {
        $order     = Order::findOrFail($id);
        $cashierId = Auth::id();

        if (! $order->isPending()) {
            return response()->json([
                'message' => 'El pedido no está en estado pendiente.',
            ], 422);
        }

        try {
            $confirmed = $this->confirmAction->execute($order, $cashierId);

            // Disparar job para generar PDF asíncrono (Paso 7)
            $invoice = Invoice::create([
                'order_id'        => $confirmed->id,
                'invoice_number'  => 'B001-' . str_pad($confirmed->id, 6, '0', STR_PAD_LEFT),
                'series'          => 'B001',
                'type'            => 'boleta',
                'receiver_name'   => $confirmed->customer_name,
                'receiver_document_type' => 'DNI',
                'subtotal'        => $confirmed->subtotal,
                'tax_rate'        => $confirmed->tax_rate,
                'tax_amount'      => $confirmed->tax_amount,
                'total'           => $confirmed->total,
                'pdf_status'      => 'pending',
                'issued_at'       => now()->toDateString(),
            ]);

            GenerateInvoicePdfJob::dispatch($invoice)->onQueue('pdf');

            return response()->json([
                'message'    => 'Pedido confirmado. Stock descontado.',
                'data'       => $confirmed,
                'invoice_id' => $invoice->id,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => 'No se pudo confirmar el pedido: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $orders = match ($request->query('view', 'pos')) {
            'admin' => $this->orderRepo->getPaginated(),
            default => $this->orderRepo->getActiveForPos(),
        };

        return response()->json(['data' => $orders]);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepo->findWithRelations($id);

        if (! $order) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        return response()->json(['data' => $order]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:preparing,ready,delivered,cancelled'],
        ]);

        $order   = Order::findOrFail($id);
        $updated = $this->orderRepo->updateStatus($order, $request->input('status'));

        return response()->json([
            'message' => "Estado actualizado a '{$updated->status}'.",
            'data'    => $updated,
        ]);
    }
}
