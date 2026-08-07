<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
        public Warehouse $warehouse,
        public int $currentQuantity
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Low stock alert: {$this->product->name}")
            ->line("{$this->product->name} in {$this->warehouse->name} has dropped to {$this->currentQuantity} units.")
            ->line("Reorder level is set at {$this->product->reorder_level} units.")
            ->action('View Product', url("/products/{$this->product->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'warehouse_id' => $this->warehouse->id,
            'warehouse_name' => $this->warehouse->name,
            'current_quantity' => $this->currentQuantity,
            'reorder_level' => $this->product->reorder_level,
        ];
    }
}
