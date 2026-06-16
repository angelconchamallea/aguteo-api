<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCouponRequest;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    public function validate(ValidateCouponRequest $request): JsonResponse
    {
        $code     = strtoupper(trim($request->input('code')));
        $subtotal = (int) $request->input('subtotal');
        $items    = $request->input('items');

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->is_active) {
            return response()->json(['message' => 'El cupón no existe o está inactivo.'], 422);
        }

        $now = Carbon::now();

        if ($coupon->starts_at && $coupon->starts_at->gt($now)) {
            return response()->json(['message' => 'El cupón aún no está vigente.'], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->lt($now)) {
            $formatted = $coupon->expires_at->format('d-m-Y');
            return response()->json(['message' => "El cupón expiró el {$formatted}."], 422);
        }

        if ($coupon->usage_limit !== null && $coupon->times_used >= $coupon->usage_limit) {
            return response()->json(['message' => 'El cupón ha alcanzado su límite de usos.'], 422);
        }

        if ($coupon->min_purchase_amount !== null && $subtotal < $coupon->min_purchase_amount) {
            $formatted = number_format($coupon->min_purchase_amount, 0, ',', '.');
            return response()->json([
                'message' => "El cupón requiere un mínimo de \${$formatted} en la compra.",
            ], 422);
        }

        $discountableSubtotal = $this->resolveDiscountableSubtotal($coupon, $items, $subtotal);

        $discountAmount = $this->calculateDiscount($coupon, $discountableSubtotal);

        return response()->json([
            'data' => [
                'code'            => $coupon->code,
                'type'            => $coupon->type,
                'value'           => $coupon->value,
                'discount_amount' => $discountAmount,
            ],
        ]);
    }

    private function resolveDiscountableSubtotal(Coupon $coupon, array $items, int $subtotal): int
    {
        $restrictedCategories = $coupon->categories()->pluck('categories.id')->toArray();
        $restrictedBrands     = $coupon->brands()->pluck('brands.id')->toArray();

        if (empty($restrictedCategories) && empty($restrictedBrands)) {
            return $subtotal;
        }

        $productIds = collect($items)->pluck('product_id')->toArray();
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Resolve all descendant IDs for restricted categories
        $allowedCategoryIds = [];
        foreach ($restrictedCategories as $catId) {
            $cat = Category::find($catId);
            if ($cat) {
                $allowedCategoryIds = array_merge($allowedCategoryIds, $cat->getDescendantIds());
            }
        }

        $discountable = 0;
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) continue;

            $qualifies = false;
            if (!empty($allowedCategoryIds) && in_array($product->category_id, $allowedCategoryIds)) {
                $qualifies = true;
            }
            if (!empty($restrictedBrands) && $product->brand_id && in_array($product->brand_id, $restrictedBrands)) {
                $qualifies = true;
            }

            if ($qualifies) {
                $discountable += $product->price * (int) $item['quantity'];
            }
        }

        return $discountable;
    }

    private function calculateDiscount(Coupon $coupon, int $subtotal): int
    {
        return match ($coupon->type) {
            'percentage'   => (int) round($subtotal * $coupon->value / 100),
            'fixed_amount' => min($coupon->value, $subtotal),
            default        => 0, // free_shipping — descuento en subtotal = 0
        };
    }
}
