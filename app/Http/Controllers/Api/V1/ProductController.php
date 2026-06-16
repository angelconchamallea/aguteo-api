<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const DEFAULT_PER_PAGE = 24;
    private const MAX_PER_PAGE     = 48;

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->integer('per_page') ?: self::DEFAULT_PER_PAGE), self::MAX_PER_PAGE);

        $query = Product::where('status', 'active')
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug,color_token,depth,parent_id,path',
                'category.parent:id,name,slug,color_token',
                'ageStages:id,slug,color_token',
                'coverImage:id,product_id,path,alt_text',
            ])
            ->withCount(['variants as stock_variants_count' => fn($q) => $q->where('stock', '>', 0)]);

        // Filtro por categoría (toda la rama descendiente)
        if ($slug = $request->string('category')->toString()) {
            $category = Category::where('slug', $slug)->first();
            if ($category) {
                $ids = $category->getDescendantIds();
                $query->whereIn('category_id', $ids);
            }
        }

        // Filtro por etapa del bebé
        if ($stage = $request->string('stage')->toString()) {
            $query->whereHas('ageStages', fn($q) => $q->where('slug', $stage));
        }

        // Filtro por marca
        if ($brand = $request->string('brand')->toString()) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $brand));
        }

        // Filtro por precio
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->input('max_price'));
        }

        // Filtro featured
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        // Búsqueda por texto
        if ($search = $request->string('search')->toString()) {
            $query->where(fn($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('sku', 'ilike', "%{$search}%")
                ->orWhere('short_description', 'ilike', "%{$search}%")
            );
        }

        // Ordenamiento
        match ($request->input('sort')) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'featured'   => $query->orderByDesc('featured')->orderByDesc('created_at'),
            default      => $query->orderByDesc('created_at'), // newest
        };

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => ProductListResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug,color_token,depth,parent_id,path',
                'category.parent:id,name,slug,color_token',
                'ageStages:id,slug,color_token',
                'images:id,product_id,path,alt_text,sort_order',
                'variants:id,product_id,size,color,sku,stock,price_override',
                'tags:id,name,slug',
                'guides:id,title,slug',
            ])
            ->firstOrFail();

        return response()->json(['data' => new ProductDetailResource($product)]);
    }
}
