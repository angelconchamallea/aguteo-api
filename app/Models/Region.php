<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['name', 'code', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }
}
