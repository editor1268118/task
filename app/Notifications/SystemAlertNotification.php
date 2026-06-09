<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $message,
        protected ?string $url = null,
        protected string $module = 'system',
        protected string $action = 'updated',
        protected string $priority = 'normal',
        protected bool $sendMail = true,
        protected array $meta = []
    ) {
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($this->sendMail && !empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->markdown('emails.notifications.system-alert', [
                'notifiable' => $notifiable,
                'title' => $this->title,
                'messageText' => $this->message,
                'url' => $this->url,
                'module' => $this->module,
                'action' => $this->action,
                'priority' => $this->priority,
            ]);
    }

    public function toArray($notifiable): array
    {
        return array_filter([
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'module' => $this->module,
            'action' => $this->action,
            'priority' => $this->priority,
            'meta' => $this->meta,
            'sent_at' => now()->toIso8601String(),
        ], fn ($value) => $value !== null);
    }
}
