<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomaticDiscount extends Model
{
    protected $fillable = [
        'name', 'type', 'value', 'conditions', 'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'conditions' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
