<?php

namespace NotificationChannels\SuprSend;

use Illuminate\Notifications\Notification;
use NotificationChannels\SuprSend\Exceptions\CouldNotSendNotification;

class SuprSendChannel
{
    protected SuprSendClient $client;

    public function __construct(?SuprSendClient $client = null)
    {
        $this->client = $client ?? new SuprSendClient();
    }

    /**
     * Send the given notification.
     *
     * @throws \NotificationChannels\SuprSend\Exceptions\CouldNotSendNotification
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        /** @var SuprSendMessage $message */
        $message = $notification->toSuprSend($notifiable);
        $message->notifiable($notifiable);

        $distinctId = $this->createUniqueIdFor($notifiable->email);

        $this->appendNewUser($notifiable->email, $distinctId);

        try {
            $this->client->triggerEvent($message, $distinctId);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        }
    }

    protected function appendNewUser(string $email, string $distinctId): void
    {
        try {
            $this->client->appendUser($email, $distinctId);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        }
    }

    protected function createUniqueIdFor(string $email): string
    {
        $email = trim(strtolower($email));

        return hash('sha256', $email.'suprsend');
    }
}
