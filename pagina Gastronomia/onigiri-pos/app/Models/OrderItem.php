<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id',
        'product_name', 'product_sku',
        'quantity', 'unit_price', 'discount_per_unit', 'line_total',
        'customization_notes', 'item_status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'       => 'decimal:2',
            'discount_per_unit'=> 'decimal:2',
            'line_total'       => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
