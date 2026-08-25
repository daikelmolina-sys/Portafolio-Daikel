<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'order_id', 'invoice_number', 'series', 'type',
        'receiver_name', 'receiver_document_type', 'receiver_document_number', 'receiver_address',
        'subtotal', 'discount_amount', 'tax_rate', 'tax_amount', 'total',
        'pdf_status', 'pdf_path', 'notes', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
            'issued_at'       => 'date',
        ];
    }

    public function isPdfReady(): bool
    {
        return $this->pdf_status === 'generated' && $this->pdf_path !== null;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
