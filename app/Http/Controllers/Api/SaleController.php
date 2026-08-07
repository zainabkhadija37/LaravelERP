<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $sales)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:sales.view')->only(['index', 'show']);
        $this->middleware('can:sales.create')->only('store');
        $this->middleware('can:sales.complete')->only('complete');
        $this->middleware('can:sales.cancel')->only('cancel');
    }

    public function index(Request $request)
    {
        $sales = $this->sales->paginate(
            $request->only(['search', 'status', 'from', 'to']),
            (int) $request->get('per_page', 15)
        );

        return SaleResource::collection($sales);
    }

    public function store(StoreSaleRequest $request): SaleResource
    {
        $sale = $this->sales->create($request->validated(), $request->user());

        return new SaleResource($sale);
    }

    public function show(Sale $sale): SaleResource
    {
        return new SaleResource($sale->load(['items.product', 'customer', 'warehouse', 'createdBy']));
    }

    public function complete(Sale $sale): SaleResource
    {
        return new SaleResource($this->sales->complete($sale));
    }

    public function recordPayment(Request $request, Sale $sale): SaleResource
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01']]);

        return new SaleResource($this->sales->recordPayment($sale, $data['amount']));
    }

    public function cancel(Sale $sale): SaleResource
    {
        return new SaleResource($this->sales->cancel($sale));
    }
}
