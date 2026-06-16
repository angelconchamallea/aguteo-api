<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryDetailResource;
use App\Http\Resources\CategoryTreeResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::roots()
            ->active()
            ->with(['children' => fn($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => CategoryTreeResource::collection($categories)]);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->with(['children' => fn($q) => $q->active()->orderBy('sort_order')])
            ->firstOrFail();

        return response()->json(['data' => new CategoryDetailResource($category)]);
    }
}
