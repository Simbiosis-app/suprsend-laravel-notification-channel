<?php

namespace NotificationChannels\SuprSend;

class SuprSendMessage
{
    protected array $data = [];
    protected string $eventName;

    public function __construct()
    {
        //
    }

    public static function create(): self
    {
        return new self();
    }

    public function eventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function data(array $data): self
    {
        $this->data = $data;

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
