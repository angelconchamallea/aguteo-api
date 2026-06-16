<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GuideDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'slug'            => $this->slug,
            'excerpt'         => $this->excerpt,
            'body'            => $this->body,
            'cover_image_url' => $this->cover_image ? Storage::url($this->cover_image) : null,
            'published_at'    => $this->published_at?->toIso8601String(),
            'age_stage'       => $this->whenLoaded('ageStage', fn() => [
                'id'          => $this->ageStage->id,
                'name'        => $this->ageStage->name,
                'slug'        => $this->ageStage->slug,
                'tagline'     => $this->ageStage->tagline,
                'color_token' => $this->ageStage->color_token,
            ]),
            'products'        => $this->whenLoaded('products', fn() =>
                ProductListResource::collection($this->products)
            ),
        ];
    }
}
