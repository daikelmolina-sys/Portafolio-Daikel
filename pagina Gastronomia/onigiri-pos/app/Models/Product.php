<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'short_description', 'long_description',
        'price', 'compare_price', 'main_image_path', 'gallery_images',
        'tracks_ingredients', 'direct_stock', 'is_available', 'is_featured',
        'sort_order', 'tags', 'prep_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'compare_price'      => 'decimal:2',
            'gallery_images'     => 'array',
            'tags'               => 'array',
            'tracks_ingredients' => 'boolean',
            'is_available'       => 'boolean',
            'is_featured'        => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------
    public function hasDiscount(): bool
    {
        return $this->compare_price !== null && $this->compare_price > $this->price;
    }

    public function discountPercentage(): int
    {
        if (! $this->hasDiscount()) return 0;
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    // -------------------------------------------------------------------------
    // RELACIONES
    // -------------------------------------------------------------------------
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipes')
                    ->withPivot(['quantity_per_unit', 'unit', 'notes'])
                    ->withTimestamps();
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
