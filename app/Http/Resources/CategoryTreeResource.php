<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryTreeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id'       => $this->id,
            'name'     => $this->name,
            'slug'     => $this->slug,
            'depth'    => $this->depth,
            'children' => CategoryTreeResource::collection($this->whenLoaded('children')),
        ];

        if ($this->depth === 0) {
            $data['color_token'] = $this->color_token;
            $data['icon']        = $this->icon;
        }

        return $data;
    }
}
