<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgeStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'tagline'    => $this->tagline,
            'color_token'=> $this->color_token,
            'min_months' => $this->min_months,
            'max_months' => $this->max_months,
        ];
    }
}
