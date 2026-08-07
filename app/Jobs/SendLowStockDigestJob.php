<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockDigestNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scheduled (see routes/console.php) to run daily and roll every
 * below-reorder-level product into a single digest email per manager,
 * instead of firing one notification per product per day.
 */
class SendLowStockDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lowStockProducts = Product::active()->lowStock()->with('warehouses')->get();

        if ($lowStockProducts->isEmpty()) {
            return;
        }

        $managers = User::role(['Admin', 'Manager'])->get();

        foreach ($managers as $manager) {
            $manager->notify(new LowStockDigestNotification($lowStockProducts));
        }
    }
}
