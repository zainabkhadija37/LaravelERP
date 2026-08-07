<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    // public function __construct(private readonly ProductService $products)
    // {
    //     $this->middleware('auth:sanctum');
    //     $this->middleware('can:products.view')->only(['index', 'show']);
    //     $this->middleware('can:products.create')->only('store');
    //     $this->middleware('can:products.update')->only('update');
    //     $this->middleware('can:products.delete')->only('destroy');
    //     $this->middleware('can:products.export')->only('export');
    // }

    public function __construct(
        private readonly ProductService $products
    ) {}

    public static function middleware(): array
    {
        return [
            'auth:sanctum',
            new Middleware('can:products.view', only: ['index', 'show']),
            new Middleware('can:products.create', only: ['store']),
            new Middleware('can:products.update', only: ['update']),
            new Middleware('can:products.delete', only: ['destroy']),
            new Middleware('can:products.export', only: ['export']),
        ];
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->products->paginate(
            $request->only(['search', 'category_id', 'low_stock', 'is_active']),
            (int) $request->get('per_page', 15)
        );

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $product = $this->products->create($request->validated());

        return new ProductResource($product);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'supplier', 'warehouses']));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = $this->products->update($product, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(Product $product): \Illuminate\Http\Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }

    public function export(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new ProductsExport($request->only(['search', 'category_id'])),
            'products-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
