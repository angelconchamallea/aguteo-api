<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function communes(): BelongsToMany
    {
        return $this->belongsToMany(Commune::class, 'shipping_zone_communes');
    }
}
