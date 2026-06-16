<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuideDetailResource;
use App\Http\Resources\GuideListResource;
use App\Models\Guide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Guide::published()->with('ageStage:id,name,slug,color_token');

        if ($stage = $request->string('stage')->toString()) {
            $query->whereHas('ageStage', fn($q) => $q->where('slug', $stage));
        }

        $guides = $query->orderByDesc('published_at')->get();

        return response()->json(['data' => GuideListResource::collection($guides)]);
    }

    public function show(string $slug): JsonResponse
    {
        $guide = Guide::published()
            ->where('slug', $slug)
            ->with([
                'ageStage:id,name,slug,tagline,color_token',
                'products' => fn($q) => $q->where('status', 'active')
                    ->with([
                        'brand:id,name,slug',
                        'category:id,name,slug,color_token,depth,parent_id,path',
                        'category.parent:id,name,slug,color_token',
                        'ageStages:id,slug,color_token',
                        'coverImage:id,product_id,path,alt_text',
                    ])
                    ->withCount(['variants as stock_variants_count' => fn($v) => $v->where('stock', '>', 0)]),
            ])
            ->firstOrFail();

        return response()->json(['data' => new GuideDetailResource($guide)]);
    }
}
