<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1a1a1a; }

        .invoice-container { padding: 40px; max-width: 800px; margin: 0 auto; }

        /* HEADER */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; border-bottom: 3px solid #1a1a1a; padding-bottom: 20px; }
        .brand-name { font-size: 28px; font-weight: 900; letter-spacing: -1px; }
        .brand-tagline { font-size: 11px; color: #666; margin-top: 4px; }
        .invoice-meta { text-align: right; }
        .invoice-number { font-size: 20px; font-weight: 700; color: #c0392b; }
        .invoice-date { font-size: 11px; color: #666; margin-top: 4px; }

        /* PARTES */
        .parties { display: flex; gap: 40px; margin-bottom: 28px; }
        .party { flex: 1; }
        .party-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 8px; }
        .party-name { font-size: 14px; font-weight: 700; }
        .party-detail { font-size: 11px; color: #555; margin-top: 3px; }

        /* TABLA */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .items-table th { background: #1a1a1a; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .items-table th:last-child { text-align: right; }
        .items-table td { padding: 10px 12px; border-bottom: 1px solid #eee; font-size: 12px; }
        .items-table td:last-child { text-align: right; font-weight: 600; }
        .items-table tr:nth-child(even) td { background: #f9f9f9; }

        /* TOTALES */
        .totals { display: flex; justify-content: flex-end; margin-bottom: 28px; }
        .totals-box { min-width: 260px; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; border-bottom: 1px solid #eee; }
        .total-row.final { font-size: 16px; font-weight: 900; border-bottom: none; border-top: 2px solid #1a1a1a; margin-top: 4px; padding-top: 10px; }

        /* PAGO */
        .payment-info { background: #f5f5f5; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 12px; }
        .payment-label { font-weight: 700; margin-bottom: 4px; }

        /* FOOTER */
        .footer { text-align: center; font-size: 10px; color: #888; border-top: 1px solid #eee; padding-top: 16px; }
        .footer strong { color: #1a1a1a; }
    </style>
</head>
<body>
<div class="invoice-container">

    <!-- ENCABEZADO -->
    <div class="header">
        <div>
            <div class="brand-name">🍙 Onigiri POS</div>
            <div class="brand-tagline">Comida rápida asiática · El sabor de Japón</div>
        </div>
        <div class="invoice-meta">
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div class="invoice-date">Emitido: {{ $invoice->issued_at->format('d/m/Y') }}</div>
            <div class="invoice-date">Orden: {{ $order->order_number }}</div>
        </div>
    </div>

    <!-- EMISOR / RECEPTOR -->
    <div class="parties">
        <div class="party">
            <div class="party-label">Emisor</div>
            <div class="party-name">Onigiri Express S.A.C.</div>
            <div class="party-detail">RUC: 20123456789</div>
            <div class="party-detail">Lima, Perú</div>
        </div>
        <div class="party">
            <div class="party-label">Cliente</div>
            <div class="party-name">{{ $invoice->receiver_name }}</div>
            @if($invoice->receiver_document_number)
                <div class="party-detail">{{ $invoice->receiver_document_type }}: {{ $invoice->receiver_document_number }}</div>
            @endif
            @if($invoice->receiver_address)
                <div class="party-detail">{{ $invoice->receiver_address }}</div>
            @endif
        </div>
    </div>

    <!-- TABLA DE ITEMS -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th style="text-align:center">Cant.</th>
                <th style="text-align:right">P. Unit.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->product_name }}
                    @if($item->customization_notes)
                        <br><small style="color:#888">{{ $item->customization_notes }}</small>
                    @endif
                </td>
                <td style="text-align:center">{{ $item->quantity }}</td>
                <td style="text-align:right">S/ {{ number_format($item->unit_price, 2) }}</td>
                <td>S/ {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTALES -->
    <div class="totals">
        <div class="totals-box">
            <div class="total-row">
                <span>Subtotal</span>
                <span>S/ {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="total-row">
                <span>Descuento</span>
                <span style="color:#c0392b">- S/ {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-row">
                <span>IGV ({{ number_format($invoice->tax_rate, 0) }}%)</span>
                <span>S/ {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            <div class="total-row final">
                <span>TOTAL</span>
                <span>S/ {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN DE PAGO -->
    @if($order->payment_method)
    <div class="payment-info">
        <div class="payment-label">Método de pago: {{ strtoupper($order->payment_method) }}</div>
        @if($order->amount_received)
            <div>Recibido: S/ {{ number_format($order->amount_received, 2) }}
            @if($order->change_amount > 0)
                · Vuelto: S/ {{ number_format($order->change_amount, 2) }}
            @endif
            </div>
        @endif
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p><strong>¡Gracias por tu compra!</strong> Vuelve pronto 🍙</p>
        <p style="margin-top:6px">Onigiri Express S.A.C. · Lima, Perú · contacto@onigiri-pos.com</p>
        <p style="margin-top:4px">Este documento es una {{ $invoice->type }} electrónica</p>
    </div>

</div>
</body>
</html>
