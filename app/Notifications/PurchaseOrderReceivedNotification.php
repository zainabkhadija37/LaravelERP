<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public PurchaseOrder $purchaseOrder) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Purchase order {$this->purchaseOrder->po_number} received")
            ->line("Purchase order {$this->purchaseOrder->po_number} has been received and stock has been updated.")
            ->action('View Purchase Order', url("/purchase-orders/{$this->purchaseOrder->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_order_id' => $this->purchaseOrder->id,
            'po_number' => $this->purchaseOrder->po_number,
            'grand_total' => $this->purchaseOrder->grand_total,
        ];
    }
}
