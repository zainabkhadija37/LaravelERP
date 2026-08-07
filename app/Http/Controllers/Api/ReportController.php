<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSalesReportJob;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:reports.view');
    }

    public function sales(Request $request)
    {
        return $this->reports->salesReport($request->get('from'), $request->get('to'));
    }

    public function inventoryValuation()
    {
        return $this->reports->inventoryValuationReport();
    }

    public function stockMovements(Request $request)
    {
        return $this->reports->stockMovementReport(
            $request->integer('product_id') ?: null,
            $request->integer('warehouse_id') ?: null
        );
    }

    public function lowStock()
    {
        return $this->reports->lowStockReport();
    }

    /**
     * Queue a large sales export instead of generating it synchronously,
     * demonstrating the queued-job export pattern for big date ranges.
     */
    public function queueSalesExport(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        GenerateSalesReportJob::dispatch($request->user(), $data['from'] ?? null, $data['to'] ?? null);

        return response()->json(['message' => 'Report generation queued. You will be notified when it is ready.']);
    }
}
