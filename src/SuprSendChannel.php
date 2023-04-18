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

        $message->users([
            [
                'distinct_id' => $this->createUniqueIdFor($notifiable->email),
                '$email' => $notifiable->email,
            ],
        ]);

        try {
            $this->client->triggerWorkflow($message);
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
