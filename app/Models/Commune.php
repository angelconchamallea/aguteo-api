<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Commune extends Model
{
    protected $fillable = ['region_id', 'name'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function shippingZones(): BelongsToMany
    {
        return $this->belongsToMany(ShippingZone::class, 'shipping_zone_communes');
    }
}
