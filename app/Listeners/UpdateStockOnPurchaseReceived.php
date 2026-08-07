<?php

namespace App\Listeners;

use App\Events\PurchaseOrderReceived;
use App\Notifications\PurchaseOrderReceivedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Note: the actual stock increment happens synchronously inside
 * PurchaseOrderService::receive() via StockService, because the caller
 * needs the up-to-date quantity immediately. This listener only handles
 * the side effect of notifying managers, which is safe to defer to a queue.
 */
class UpdateStockOnPurchaseReceived implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PurchaseOrderReceived $event): void
    {
        $managers = User::role(['Admin', 'Manager'])->get();

        foreach ($managers as $manager) {
            $manager->notify(new PurchaseOrderReceivedNotification($event->purchaseOrder));
        }
    }
}
