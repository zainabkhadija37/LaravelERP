<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| All routes are versioned under /api/v1 and, aside from auth, protected
| by Sanctum + Spatie permission middleware declared in each controller.
*/

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::get('products/export', [ProductController::class, 'export']);
        Route::apiResource('products', ProductController::class);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('customers', CustomerController::class);

        Route::apiResource('purchase-orders', PurchaseOrderController::class)->only(['index', 'store', 'show']);
        Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
        Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);

        Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);
        Route::post('sales/{sale}/complete', [SaleController::class, 'complete']);
        Route::post('sales/{sale}/payments', [SaleController::class, 'recordPayment']);
        Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel']);

        Route::apiResource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'store']);

        Route::prefix('reports')->group(function () {
            Route::get('sales', [ReportController::class, 'sales']);
            Route::get('inventory-valuation', [ReportController::class, 'inventoryValuation']);
            Route::get('stock-movements', [ReportController::class, 'stockMovements']);
            Route::get('low-stock', [ReportController::class, 'lowStock']);
            Route::post('sales/export', [ReportController::class, 'queueSalesExport']);
        });

        Route::get('activity-log', [ActivityLogController::class, 'index']);
    });
});
