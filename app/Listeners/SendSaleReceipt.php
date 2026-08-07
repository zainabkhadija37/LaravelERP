<?php

namespace App\Listeners;

use App\Events\SaleCompleted;
use App\Notifications\SaleReceiptNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendSaleReceipt implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale->loadMissing('customer');

        if ($sale->customer?->email) {
            Notification::route('mail', $sale->customer->email)
                ->notify(new SaleReceiptNotification($sale));
        }
    }
}
