<?php

namespace NotificationChannels\SuprSend;

class SuprSendMessage
{
    protected array $data = [];
    protected string $eventName;

    public static function create(): static
    {
        return new static();
    }

    public function eventName(string $eventName): static
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function notifiable(mixed $notifiable): static
    {
        $this->data['notifiable'] = $notifiable;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }
}
