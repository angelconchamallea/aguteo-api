<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'slug', 'short_description', 'description',
        'brand_id', 'category_id', 'price', 'compare_at_price', 'cost_price',
        'has_variants', 'stock', 'low_stock_threshold', 'status', 'featured', 'weight_grams',
    ];

    protected $hidden = [
        'cost_price',
        'low_stock_threshold',
    ];

    protected $casts = [
        'price' => 'integer',
        'compare_at_price' => 'integer',
        'cost_price' => 'integer',
        'has_variants' => 'boolean',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'featured' => 'boolean',
        'weight_grams' => 'integer',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function ageStages(): BelongsToMany
    {
        return $this->belongsToMany(AgeStage::class, 'product_age_stage');
    }

    public function guides(): BelongsToMany
    {
        return $this->belongsToMany(Guide::class, 'guide_product');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->has_variants) {
            return $this->variants()->sum('stock');
        }

        return $this->stock ?? 0;
    }
}
