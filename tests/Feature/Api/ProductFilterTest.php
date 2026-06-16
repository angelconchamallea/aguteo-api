<?php

namespace Tests\Feature\Api;

use App\Models\AgeStage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeRoot(string $name, string $slug, string $color = '#AABBCC'): Category
    {
        $cat = Category::create([
            'name'        => $name,
            'slug'        => $slug,
            'color_token' => $color,
            'depth'       => 0,
            'path'        => '0', // placeholder; set after save
            'is_active'   => true,
        ]);
        $cat->update(['path' => (string) $cat->id]);
        return $cat->fresh();
    }

    private function makeChild(Category $parent, string $name, string $slug): Category
    {
        $child = Category::create([
            'name'      => $name,
            'slug'      => $slug,
            'parent_id' => $parent->id,
            'depth'     => 1,
            'path'      => "{$parent->id}/0",
            'is_active' => true,
        ]);
        $child->update(['path' => "{$parent->id}/{$child->id}"]);
        return $child->fresh();
    }

    private function makeProduct(array $attrs = []): Product
    {
        return Product::factory()->active()->create($attrs);
    }

    // -------------------------------------------------------------------------
    // Pagination meta
    // -------------------------------------------------------------------------

    public function test_list_returns_correct_meta(): void
    {
        $brand    = Brand::factory()->create();
        $root     = $this->makeRoot('Ropa', 'ropa');

        Product::factory()->active()->count(3)->create([
            'category_id' => $root->id,
            'brand_id'    => $brand->id,
        ]);

        $response = $this->getJson('/api/v1/products?per_page=2');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);
    }

    // -------------------------------------------------------------------------
    // Filtro: category (nodo hoja)
    // -------------------------------------------------------------------------

    public function test_filter_by_leaf_category_returns_only_that_category(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa');
        $conj  = $this->makeChild($root, 'Conjuntos', 'conjuntos');
        $pij   = $this->makeChild($root, 'Pijamas', 'pijamas');
        $brand = Brand::factory()->create();

        $p1 = $this->makeProduct(['category_id' => $conj->id, 'brand_id' => $brand->id]);
        $p2 = $this->makeProduct(['category_id' => $pij->id,  'brand_id' => $brand->id]);

        $response = $this->getJson('/api/v1/products?category=conjuntos');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $p1->slug);
    }

    // -------------------------------------------------------------------------
    // Filtro: category (nodo raíz incluye toda la rama)
    // -------------------------------------------------------------------------

    public function test_filter_by_root_category_includes_descendants(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa');
        $otro  = $this->makeRoot('Juguetes', 'juguetes');
        $conj  = $this->makeChild($root, 'Conjuntos', 'conjuntos');
        $brand = Brand::factory()->create();

        $this->makeProduct(['category_id' => $conj->id, 'brand_id' => $brand->id]);
        $this->makeProduct(['category_id' => $conj->id, 'brand_id' => $brand->id]);
        $this->makeProduct(['category_id' => $otro->id, 'brand_id' => $brand->id]);

        $response = $this->getJson('/api/v1/products?category=ropa');

        $response->assertOk()->assertJsonPath('meta.total', 2);
    }

    // -------------------------------------------------------------------------
    // Filtro: stage
    // -------------------------------------------------------------------------

    public function test_filter_by_age_stage(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa');
        $brand = Brand::factory()->create();
        $stage = AgeStage::create([
            'name'        => '0-3 meses',
            'slug'        => '0-3m',
            'min_months'  => 0,
            'max_months'  => 3,
            'color_token' => '#7DD9D4',
            'tagline'     => 'Recién llegado',
            'sort_order'  => 0,
        ]);

        $p1 = $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id]);
        $p2 = $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id]);
        $p1->ageStages()->attach($stage->id);

        $response = $this->getJson('/api/v1/products?stage=0-3m');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $p1->slug);
    }

    // -------------------------------------------------------------------------
    // Filtro: brand
    // -------------------------------------------------------------------------

    public function test_filter_by_brand(): void
    {
        $root   = $this->makeRoot('Ropa', 'ropa');
        $brandA = Brand::factory()->create(['slug' => 'amma']);
        $brandB = Brand::factory()->create();

        $p1 = $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brandA->id]);
        $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brandB->id]);

        $response = $this->getJson('/api/v1/products?brand=amma');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $p1->slug);
    }

    // -------------------------------------------------------------------------
    // Filtro: precio
    // -------------------------------------------------------------------------

    public function test_filter_by_price_range(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa');
        $brand = Brand::factory()->create();

        $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id, 'price' => 3000]);
        $p2 = $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id, 'price' => 10000]);
        $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id, 'price' => 50000]);

        $response = $this->getJson('/api/v1/products?min_price=5000&max_price=20000');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $p2->slug);
    }

    // -------------------------------------------------------------------------
    // Filtros combinados
    // -------------------------------------------------------------------------

    public function test_combined_filters(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa');
        $conj  = $this->makeChild($root, 'Conjuntos', 'conjuntos');
        $brandA = Brand::factory()->create(['slug' => 'aguteo-babys']);
        $brandB = Brand::factory()->create();
        $stage  = AgeStage::create([
            'name' => '0-3 meses', 'slug' => '0-3m',
            'min_months' => 0, 'max_months' => 3,
            'color_token' => '#7DD9D4', 'tagline' => 'Recién llegado', 'sort_order' => 0,
        ]);

        // Matches: ropa/conj + brandA + stage
        $match = $this->makeProduct([
            'category_id' => $conj->id, 'brand_id' => $brandA->id, 'price' => 12000,
        ]);
        $match->ageStages()->attach($stage->id);

        // Doesn't match (wrong brand)
        $p2 = $this->makeProduct(['category_id' => $conj->id, 'brand_id' => $brandB->id, 'price' => 12000]);
        $p2->ageStages()->attach($stage->id);

        // Doesn't match (wrong stage — no stage)
        $this->makeProduct(['category_id' => $conj->id, 'brand_id' => $brandA->id, 'price' => 12000]);

        $response = $this->getJson('/api/v1/products?category=ropa&stage=0-3m&brand=aguteo-babys');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $match->slug);
    }

    // -------------------------------------------------------------------------
    // Ordenamiento
    // -------------------------------------------------------------------------

    public function test_sort_price_asc(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa');
        $brand = Brand::factory()->create();

        $cheap     = $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id, 'price' => 5000]);
        $expensive = $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id, 'price' => 20000]);
        $mid       = $this->makeProduct(['category_id' => $root->id, 'brand_id' => $brand->id, 'price' => 10000]);

        $response = $this->getJson('/api/v1/products?sort=price_asc');

        $response->assertOk();
        $prices = collect($response->json('data'))->pluck('price')->toArray();
        $this->assertEquals([5000, 10000, 20000], $prices);
    }

    // -------------------------------------------------------------------------
    // Shape del detalle
    // -------------------------------------------------------------------------

    public function test_product_detail_shape_is_correct(): void
    {
        $root     = $this->makeRoot('Ropa', 'ropa', '#F8C8D4');
        $conj     = $this->makeChild($root, 'Conjuntos', 'conjuntos');
        $brand    = Brand::factory()->create();

        $product = $this->makeProduct([
            'slug'              => 'conjunto-test',
            'category_id'       => $conj->id,
            'brand_id'          => $brand->id,
            'has_variants'      => true,
            'stock'             => null,
            'compare_at_price'  => 15000,
            'price'             => 12000,
        ]);

        ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 5]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'sku', 'name', 'slug', 'short_description', 'description',
                    'price', 'compare_at_price', 'discount_percent',
                    'has_variants', 'in_stock', 'rating', 'reviews_count',
                    'brand', 'category', 'age_stages',
                    'images', 'variants', 'tags', 'related_guides',
                    'cover_image_url', 'featured',
                ],
            ])
            // Never expose private fields
            ->assertJsonMissingPath('data.cost_price')
            ->assertJsonMissingPath('data.low_stock_threshold')
            // Computed fields
            ->assertJsonPath('data.discount_percent', 20)
            ->assertJsonPath('data.in_stock', true)
            ->assertJsonPath('data.rating', null)
            ->assertJsonPath('data.reviews_count', 0);
    }

    public function test_product_detail_category_has_breadcrumb(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa', '#F8C8D4');
        $conj  = $this->makeChild($root, 'Conjuntos', 'conjuntos');
        $brand = Brand::factory()->create();

        $product = $this->makeProduct(['slug' => 'prod-test', 'category_id' => $conj->id, 'brand_id' => $brand->id]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.category.breadcrumb.0.slug', 'ropa')
            ->assertJsonPath('data.category.breadcrumb.1.slug', 'conjuntos')
            ->assertJsonPath('data.category.color_token', '#F8C8D4');
    }

    public function test_product_detail_returns_404_for_draft(): void
    {
        $root  = $this->makeRoot('Ropa', 'ropa');
        $brand = Brand::factory()->create();
        $draft = Product::factory()->create([
            'category_id' => $root->id, 'brand_id' => $brand->id, 'status' => 'draft',
        ]);

        $this->getJson("/api/v1/products/{$draft->slug}")->assertNotFound();
    }

    public function test_variant_price_uses_override_when_set(): void
    {
        $root    = $this->makeRoot('Ropa', 'ropa');
        $brand   = Brand::factory()->create();
        $product = $this->makeProduct([
            'category_id'  => $root->id,
            'brand_id'     => $brand->id,
            'price'        => 10000,
            'has_variants' => true,
            'stock'        => null,
        ]);

        ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 3, 'price_override' => 12000]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 2, 'price_override' => null]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $variants = collect($response->json('data.variants'));
        $prices   = $variants->pluck('price')->sort()->values()->toArray();

        $this->assertEquals([10000, 12000], $prices);
    }
}
