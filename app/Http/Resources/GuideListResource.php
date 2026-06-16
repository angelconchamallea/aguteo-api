<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GuideListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'slug'            => $this->slug,
            'excerpt'         => $this->excerpt,
            'cover_image_url' => $this->cover_image ? Storage::url($this->cover_image) : null,
            'age_stage'       => $this->whenLoaded('ageStage', fn() => [
                'id'          => $this->ageStage->id,
                'name'        => $this->ageStage->name,
                'slug'        => $this->ageStage->slug,
                'color_token' => $this->ageStage->color_token,
            ]),
        ];
    }
}
