<?php

namespace NotificationChannels\SuprSend;

use NotificationChannels\SuprSend\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;

class SuprSendChannel
{
    protected SuprSendClient $client;

    public function __construct()
    {
        $this->client = new SuprSendClient();
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\SuprSend\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var SuprSendMessage $message */
        $message = $notification->toSuprSend($notifiable);

        $distinctId = $this->createUniqueIdFor($notifiable->email);

        $this->appendNewUser($notifiable->email, $distinctId);

        try {
            $this->client->triggerEvent($message, $distinctId);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        }
    }

    protected function appendNewUser(string $email, string $distinctId)
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

        return hash('sha256', $email . 'suprsend');
    }
}
