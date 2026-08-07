<?php

namespace App\Listeners;

use App\Events\StockLevelLow;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyLowStock implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 0;

    public function handle(StockLevelLow $event): void
    {
        $recipients = User::role(['Admin', 'Manager'])->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new LowStockNotification(
                $event->product,
                $event->warehouse,
                $event->currentQuantity
            ));
        }
    }
}
