<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'depth'       => $this->depth,
            'color_token' => $this->resolvedColorToken(),
            'breadcrumb'  => $this->buildBreadcrumb(),
            'children'    => CategoryTreeResource::collection($this->whenLoaded('children')),
        ];
    }

    private function resolvedColorToken(): ?string
    {
        if ($this->depth === 0) {
            return $this->color_token;
        }

        $ids = explode('/', $this->path ?? '');
        if (count($ids) >= 1) {
            $root = Category::find((int) $ids[0]);
            return $root?->color_token;
        }

        return null;
    }

    private function buildBreadcrumb(): array
    {
        if (!$this->path) {
            return [['name' => $this->name, 'slug' => $this->slug]];
        }

        $ids  = array_map('intval', explode('/', $this->path));
        $map  = Category::whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)
            ->filter(fn($id) => isset($map[$id]))
            ->map(fn($id) => ['name' => $map[$id]->name, 'slug' => $map[$id]->slug])
            ->values()
            ->all();
    }
}
