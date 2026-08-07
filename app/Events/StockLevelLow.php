<?php

namespace App\Events;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockLevelLow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public Warehouse $warehouse,
        public int $currentQuantity
    ) {}
}
