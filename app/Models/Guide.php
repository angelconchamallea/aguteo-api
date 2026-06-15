<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guide extends Model
{
    protected $fillable = [
        'age_stage_id', 'title', 'slug', 'excerpt', 'body',
        'cover_image', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function ageStage(): BelongsTo
    {
        return $this->belongsTo(AgeStage::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'guide_product');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
