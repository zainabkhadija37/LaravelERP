<?php

use App\Jobs\SendLowStockDigestJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily digest of every product at or below reorder level, run once a day
// at 07:00 so managers see it first thing in the morning.
Schedule::job(new SendLowStockDigestJob)->dailyAt('07:00');

// Prune Sanctum tokens that have expired, and old queued job failures.
Schedule::command('sanctum:prune-expired --hours=24')->daily();
