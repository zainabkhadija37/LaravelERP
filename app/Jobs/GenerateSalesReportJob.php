<?php

namespace App\Jobs;

use App\Exports\SalesExport;
use App\Models\User;
use App\Notifications\ReportReadyNotification;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Generates a sales report export in the background so large date ranges
 * don't tie up an HTTP worker, then notifies the requesting user with a
 * download link once the file is ready.
 */
class GenerateSalesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public User $requestedBy,
        public ?string $from = null,
        public ?string $to = null,
    ) {}

    public function handle(ReportService $reportService): void
    {
        $fileName = 'reports/sales-report-'.now()->timestamp.'.xlsx';

        Excel::store(new SalesExport($this->from, $this->to), $fileName, 'local');

        $this->requestedBy->notify(new ReportReadyNotification($fileName));
    }
}
