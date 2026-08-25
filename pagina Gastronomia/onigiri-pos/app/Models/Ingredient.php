<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'description',
        'stock_quantity', 'unit', 'min_stock_alert',
        'unit_cost', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity'  => 'decimal:3',
            'min_stock_alert' => 'decimal:3',
            'unit_cost'       => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // HELPERS DE STOCK
    // -------------------------------------------------------------------------
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert;
    }

    public function hasEnoughStock(float $required): bool
    {
        return $this->stock_quantity >= $required;
    }

    // -------------------------------------------------------------------------
    // RELACIONES
    // -------------------------------------------------------------------------
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recipes')
                    ->withPivot(['quantity_per_unit', 'unit', 'notes'])
                    ->withTimestamps();
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(IngredientMovement::class);
    }
}
