<?php

namespace NotificationChannels\SuprSend\Test\Fixtures;

use Illuminate\Notifications\Notification;
use NotificationChannels\SuprSend\SuprSendMessage;

class TestNotification extends Notification
{
    public function __construct(
        protected string $eventName = 'invoice_paid',
        protected array $data = ['invoice_number' => 'INV-1', 'amount' => 100],
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return ['suprSend'];
    }

    public function toSuprSend(mixed $notifiable): SuprSendMessage
    {
        return SuprSendMessage::create()
            ->eventName($this->eventName)
            ->data($this->data);
    }
}
