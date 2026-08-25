<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'cashier_id',
        'customer_name', 'customer_email', 'customer_phone',
        'status', 'channel', 'delivery_type',
        'delivery_address', 'delivery_notes',
        'subtotal', 'discount_amount', 'tax_rate', 'tax_amount',
        'delivery_fee', 'total',
        'payment_method', 'payment_status',
        'amount_received', 'change_amount',
        'internal_notes',
        'confirmed_at', 'ready_at', 'delivered_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'delivery_fee'    => 'decimal:2',
            'total'           => 'decimal:2',
            'amount_received' => 'decimal:2',
            'change_amount'   => 'decimal:2',
            'confirmed_at'    => 'datetime',
            'ready_at'        => 'datetime',
            'delivered_at'    => 'datetime',
            'cancelled_at'    => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // BOOT — Generar número de pedido automáticamente
    // -------------------------------------------------------------------------
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year    = now()->format('Y');
        $last    = static::whereYear('created_at', $year)->lockForUpdate()->count();
        $sequence = str_pad($last + 1, 5, '0', STR_PAD_LEFT);
        return "ONI-{$year}-{$sequence}";
    }

    // -------------------------------------------------------------------------
    // HELPERS DE ESTADO
    // -------------------------------------------------------------------------
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isConfirmed(): bool  { return $this->status === 'confirmed'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isDelivered(): bool  { return $this->status === 'delivered'; }

    // -------------------------------------------------------------------------
    // RELACIONES
    // -------------------------------------------------------------------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ingredientMovements(): HasMany
    {
        return $this->hasMany(IngredientMovement::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
