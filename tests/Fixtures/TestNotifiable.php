<?php

namespace NotificationChannels\SuprSend\Test\Fixtures;

use Illuminate\Notifications\RoutesNotifications;

class TestNotifiable
{
    use RoutesNotifications;

    public function __construct(
        public string $email = 'taylor@laravel.com',
        protected int $id = 1,
    ) {
    }

    /**
     * Laravel's NotificationFake buckets notifiables by their key.
     */
    public function getKey(): int
    {
        return $this->id;
    }
}
