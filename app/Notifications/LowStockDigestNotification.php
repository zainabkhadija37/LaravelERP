<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockDigestNotification extends Notification
{
    use Queueable;

    public function __construct(public Collection $products) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Daily low stock digest ({$this->products->count()} products)")
            ->line('The following products are at or below their reorder level:');

        foreach ($this->products->take(10) as $product) {
            $mail->line("- {$product->name} (SKU: {$product->sku})");
        }

        return $mail->action('View Inventory', url('/products?low_stock=1'));
    }

    public function toArray(object $notifiable): array
    {
        return ['product_ids' => $this->products->pluck('id')->all()];
    }
}
