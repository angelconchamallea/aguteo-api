<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponValidationTest extends TestCase
{
    use RefreshDatabase;

    private function postValidate(array $body): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/coupons/validate', $body);
    }

    private function defaultBody(array $merge = []): array
    {
        $brand    = Brand::factory()->create();
        $category = Category::create([
            'name' => 'Ropa', 'slug' => 'ropa', 'depth' => 0,
            'path' => '1', 'is_active' => true,
        ]);
        $product  = Product::factory()->active()->create([
            'category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 12990,
        ]);

        return array_merge([
            'code'     => '',
            'subtotal' => 25000,
            'items'    => [['product_id' => $product->id, 'quantity' => 2]],
        ], $merge);
    }

    // -------------------------------------------------------------------------

    public function test_valid_percentage_coupon(): void
    {
        $coupon = Coupon::factory()->percentage(10)->create(['code' => 'BIENVENIDA10']);
        $body   = $this->defaultBody(['code' => 'BIENVENIDA10', 'subtotal' => 25000]);

        $this->postValidate($body)
            ->assertOk()
            ->assertJsonPath('data.code', 'BIENVENIDA10')
            ->assertJsonPath('data.type', 'percentage')
            ->assertJsonPath('data.value', 10)
            ->assertJsonPath('data.discount_amount', 2500); // 10% of 25000
    }

    public function test_valid_fixed_amount_coupon(): void
    {
        $coupon = Coupon::factory()->fixedAmount(3000)->create(['code' => 'DESCUENTO3K']);
        $body   = $this->defaultBody(['code' => 'DESCUENTO3K', 'subtotal' => 25000]);

        $this->postValidate($body)
            ->assertOk()
            ->assertJsonPath('data.type', 'fixed_amount')
            ->assertJsonPath('data.discount_amount', 3000);
    }

    public function test_fixed_amount_cannot_exceed_subtotal(): void
    {
        $coupon = Coupon::factory()->fixedAmount(50000)->create(['code' => 'GRATIS']);
        $body   = $this->defaultBody(['code' => 'GRATIS', 'subtotal' => 5000]);

        $this->postValidate($body)
            ->assertOk()
            ->assertJsonPath('data.discount_amount', 5000); // capped at subtotal
    }

    public function test_expired_coupon_returns_422(): void
    {
        Coupon::factory()->expired()->create(['code' => 'VENCIDO']);
        $body = $this->defaultBody(['code' => 'VENCIDO']);

        $this->postValidate($body)
            ->assertUnprocessable()
            ->assertJsonPath('message', fn($msg) => str_contains($msg, 'expiró'));
    }

    public function test_not_yet_active_coupon_returns_422(): void
    {
        Coupon::factory()->notYetActive()->create(['code' => 'FUTURO']);
        $body = $this->defaultBody(['code' => 'FUTURO']);

        $this->postValidate($body)
            ->assertUnprocessable()
            ->assertJsonPath('message', fn($msg) => str_contains($msg, 'aún no está vigente'));
    }

    public function test_min_purchase_amount_not_met_returns_422(): void
    {
        Coupon::factory()->percentage(10)->withMinPurchase(50000)->create(['code' => 'MINIMO50K']);
        $body = $this->defaultBody(['code' => 'MINIMO50K', 'subtotal' => 25000]);

        $this->postValidate($body)
            ->assertUnprocessable()
            ->assertJsonPath('message', fn($msg) => str_contains($msg, 'mínimo'));
    }

    public function test_usage_limit_exceeded_returns_422(): void
    {
        Coupon::factory()->exhausted()->create(['code' => 'AGOTADO']);
        $body = $this->defaultBody(['code' => 'AGOTADO']);

        $this->postValidate($body)
            ->assertUnprocessable()
            ->assertJsonPath('message', fn($msg) => str_contains($msg, 'límite'));
    }

    public function test_inactive_coupon_returns_422(): void
    {
        Coupon::factory()->create(['code' => 'INACTIVO', 'is_active' => false]);
        $body = $this->defaultBody(['code' => 'INACTIVO']);

        $this->postValidate($body)->assertUnprocessable();
    }

    public function test_nonexistent_coupon_returns_422(): void
    {
        $body = $this->defaultBody(['code' => 'NOEXISTE']);

        $this->postValidate($body)->assertUnprocessable();
    }

    public function test_validation_requires_code_and_subtotal(): void
    {
        $this->postValidate([])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_coupon_code_is_case_insensitive(): void
    {
        Coupon::factory()->percentage(10)->create(['code' => 'MAYUS10']);
        $body = $this->defaultBody(['code' => 'mayus10', 'subtotal' => 10000]);

        $this->postValidate($body)
            ->assertOk()
            ->assertJsonPath('data.code', 'MAYUS10');
    }
}
