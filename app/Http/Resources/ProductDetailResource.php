<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductDetailResource extends JsonResource
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
            'description'       => $this->description,
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
            'images'            => $this->whenLoaded('images', fn() =>
                $this->images->map(fn($img) => [
                    'url'      => Storage::url($img->path),
                    'alt_text' => $img->alt_text,
                ])
            ),
            'variants'          => $this->whenLoaded('variants', fn() =>
                $this->variants->map(fn($v) => [
                    'id'    => $v->id,
                    'size'  => $v->size,
                    'color' => $v->color,
                    'sku'   => $v->sku,
                    'stock' => $v->stock,
                    'price' => $v->price_override ?? $this->price,
                ])
            ),
            'tags'              => $this->whenLoaded('tags', fn() =>
                $this->tags->map(fn($t) => ['name' => $t->name, 'slug' => $t->slug])
            ),
            'related_guides'    => $this->whenLoaded('guides', fn() =>
                $this->guides->map(fn($g) => ['title' => $g->title, 'slug' => $g->slug])
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
            return $this->variants->isNotEmpty()
                ? $this->variants->sum('stock') > 0
                : false;
        }

        return ($this->stock ?? 0) > 0;
    }

    private function coverImageUrl(): ?string
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return Storage::url($this->images->first()->path);
        }

        return null;
    }

    private function categoryShape($category): array
    {
        return [
            'id'          => $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'depth'       => $category->depth,
            'color_token' => $this->resolvedColorToken($category),
            'breadcrumb'  => $this->buildBreadcrumb($category),
        ];
    }

    private function resolvedColorToken($category): ?string
    {
        if ($category->depth === 0) {
            return $category->color_token;
        }

        $parent = $category->relationLoaded('parent') ? $category->parent : null;

        if ($parent) {
            return $parent->color_token;
        }

        $ids  = array_map('intval', explode('/', $category->path ?? ''));
        $root = count($ids) > 0 ? Category::find($ids[0]) : null;

        return $root?->color_token;
    }

    private function buildBreadcrumb($category): array
    {
        if (!$category->path) {
            return [['name' => $category->name, 'slug' => $category->slug]];
        }

        $ids = array_map('intval', explode('/', $category->path));
        $map = Category::whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)
            ->filter(fn($id) => isset($map[$id]))
            ->map(fn($id) => ['name' => $map[$id]->name, 'slug' => $map[$id]->slug])
            ->values()
            ->all();
    }
}
