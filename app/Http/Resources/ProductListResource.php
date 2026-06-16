<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = $this->whenLoaded('category');

        return [
            'id'                => $this->id,
            'sku'               => $this->sku,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'short_description' => $this->short_description,
            'price'             => $this->price,
            'compare_at_price'  => $this->compare_at_price,
            'discount_percent'  => $this->discountPercent(),
            'has_variants'      => $this->has_variants,
            'in_stock'          => $this->inStock(),
            'rating'            => null,
            'reviews_count'     => 0,
            'brand'             => $this->whenLoaded('brand', fn() => [
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),
            'category'          => $category ? $this->categoryShape($category) : null,
            'age_stages'        => $this->whenLoaded('ageStages', fn() =>
                $this->ageStages->map(fn($s) => ['slug' => $s->slug, 'color_token' => $s->color_token])
            ),
            'cover_image_url'   => $this->coverImageUrl(),
            'featured'          => $this->featured,
        ];
    }

    private function discountPercent(): ?int
    {
        if (!$this->compare_at_price || $this->compare_at_price <= $this->price) {
            return null;
        }

        return (int) round(($this->compare_at_price - $this->price) / $this->compare_at_price * 100);
    }

    private function inStock(): bool
    {
        if ($this->has_variants) {
            return ($this->stock_variants_count ?? 0) > 0;
        }

        return ($this->stock ?? 0) > 0;
    }

    private function coverImageUrl(): ?string
    {
        $image = $this->relationLoaded('coverImage') ? $this->coverImage : null;

        return $image ? Storage::url($image->path) : null;
    }

    private function categoryShape($category): array
    {
        $root = $this->resolveRoot($category);

        return [
            'id'          => $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'color_token' => $root['color_token'],
            'root'        => ['name' => $root['name'], 'slug' => $root['slug']],
        ];
    }

    private function resolveRoot($category): array
    {
        if ($category->depth === 0) {
            return [
                'name'        => $category->name,
                'slug'        => $category->slug,
                'color_token' => $category->color_token,
            ];
        }

        $parent = $category->relationLoaded('parent') ? $category->parent : null;

        return [
            'name'        => $parent?->name ?? $category->name,
            'slug'        => $parent?->slug ?? $category->slug,
            'color_token' => $parent?->color_token ?? $category->color_token,
        ];
    }
}
