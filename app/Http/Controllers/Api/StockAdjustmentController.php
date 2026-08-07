<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockAdjustmentService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function __construct(private readonly StockAdjustmentService $adjustments)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:stock-adjustments.view')->only('index');
        $this->middleware('can:stock-adjustments.create')->only('store');
    }

    public function index(Request $request)
    {
        return $this->adjustments->paginate(
            $request->only(['product_id', 'warehouse_id']),
            (int) $request->get('per_page', 15)
        );
    }

    public function store(StockAdjustmentRequest $request)
    {
        $data = $request->validated();

        $adjustment = $this->adjustments->adjust(
            Product::findOrFail($data['product_id']),
            Warehouse::findOrFail($data['warehouse_id']),
            $request->user(),
            $data['type'],
            $data['reason'],
            $data['quantity'],
            $data['notes'] ?? null
        );

        return response()->json($adjustment, 201);
    }
}
