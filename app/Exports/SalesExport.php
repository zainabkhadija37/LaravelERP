<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly ?string $from = null,
        private readonly ?string $to = null,
    ) {}

    public function collection()
    {
        return app(ReportService::class)->salesReport($this->from, $this->to);
    }

    public function headings(): array
    {
        return ['Invoice #', 'Date', 'Customer', 'Warehouse', 'Subtotal', 'Discount', 'Tax', 'Grand Total', 'Payment Status'];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_number,
            $sale->sale_date->toDateString(),
            $sale->customer?->name,
            $sale->warehouse?->name,
            $sale->subtotal,
            $sale->discount_total,
            $sale->tax_total,
            $sale->grand_total,
            $sale->payment_status,
        ];
    }
}
