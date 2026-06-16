<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingRateResource;
use App\Models\ShippingZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $communeId = $request->integer('commune_id');

        if (!$communeId) {
            return response()->json(['data' => []]);
        }

        $zone = ShippingZone::where('is_active', true)
            ->whereHas('communes', fn($q) => $q->where('communes.id', $communeId))
            ->with('rates')
            ->first();

        $rates = $zone?->rates ?? collect();

        return response()->json(['data' => ShippingRateResource::collection($rates)]);
    }
}
