<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeStage extends Model
{
    protected $fillable = [
        'name', 'slug', 'min_months', 'max_months', 'color_token', 'tagline', 'sort_order',
    ];

    protected $casts = [
        'min_months' => 'integer',
        'max_months' => 'integer',
        'sort_order' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_age_stage');
    }

    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class);
    }
}
