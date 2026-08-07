<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SaleReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(public Sale $sale) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Receipt for invoice {$this->sale->invoice_number}")
            ->line("Thank you! Your order total was {$this->sale->grand_total}.")
            ->action('View Invoice', url("/sales/{$this->sale->id}"));
    }
}
