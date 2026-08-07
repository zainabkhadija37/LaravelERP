<?php

namespace App\Providers;

use App\Events\PurchaseOrderReceived;
use App\Events\SaleCompleted;
use App\Events\StockLevelLow;
use App\Listeners\NotifyLowStock;
use App\Listeners\SendSaleReceipt;
use App\Listeners\UpdateStockOnPurchaseReceived;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PurchaseOrderReceived::class => [
            UpdateStockOnPurchaseReceived::class,
        ],
        SaleCompleted::class => [
            SendSaleReceipt::class,
        ],
        StockLevelLow::class => [
            NotifyLowStock::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
