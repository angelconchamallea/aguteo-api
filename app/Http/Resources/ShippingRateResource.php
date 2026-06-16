<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'price'            => $this->price,
            'free_from_amount' => $this->free_from_amount,
            'estimated_days'   => $this->estimated_days,
        ];
    }
}
