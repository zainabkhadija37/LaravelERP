<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $purchaseOrders)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:purchase-orders.view')->only(['index', 'show']);
        $this->middleware('can:purchase-orders.create')->only('store');
        $this->middleware('can:purchase-orders.approve')->only('approve');
        $this->middleware('can:purchase-orders.receive')->only('receive');
        $this->middleware('can:purchase-orders.cancel')->only('cancel');
    }

    public function index(Request $request)
    {
        $orders = $this->purchaseOrders->paginate(
            $request->only(['search', 'status']),
            (int) $request->get('per_page', 15)
        );

        return PurchaseOrderResource::collection($orders);
    }

    public function store(StorePurchaseOrderRequest $request): PurchaseOrderResource
    {
        $po = $this->purchaseOrders->create($request->validated(), $request->user());

        return new PurchaseOrderResource($po);
    }

    public function show(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource(
            $purchaseOrder->load(['items.product', 'supplier', 'warehouse', 'createdBy', 'approvedBy'])
        );
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($this->purchaseOrders->approve($purchaseOrder, $request->user()));
    }

    public function receive(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($this->purchaseOrders->receive($purchaseOrder));
    }

    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($this->purchaseOrders->cancel($purchaseOrder));
    }
}
