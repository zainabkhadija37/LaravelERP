<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public string $filePath) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your report is ready')
            ->line('The report you requested has finished generating.')
            ->action('Download Report', Storage::disk('local')->url($this->filePath));
    }

    public function toArray(object $notifiable): array
    {
        return ['file_path' => $this->filePath];
    }
}
