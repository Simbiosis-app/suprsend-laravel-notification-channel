<?php

namespace NotificationChannels\SuprSend;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class SuprSendClient
{
    public const API_URL = 'https://hub.suprsend.com/';

    protected Client $httpClient;
    protected string $workspaceKey;
    protected string $workspaceSecret;
    protected string $apiKey;

    public function __construct(?Client $httpClient = null)
    {
        $this->workspaceKey = config('services.suprSend.workspace_key');
        $this->workspaceSecret = config('services.suprSend.workspace_secret');
        $this->apiKey = config('services.suprSend.api_key');

        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => self::API_URL,
        ]);
    }

    public function triggerEvent(SuprSendMessage $suprSendMessage, string $distinctId): ResponseInterface
    {
        $payload = [
            'distinct_id' => $distinctId,
            'event' => $suprSendMessage->getEventName(),
            'properties' => $suprSendMessage->getData(),
        ];

        return $this->sendRequest(
            'event',
            json_encode($payload)
        );
    }

    public function appendUser(string $email, string $distinctId): ResponseInterface
    {
        $payload = [
            '$schema' => 2,
            'distinct_id' => $distinctId,
            '$user_operations' => [
                [
                    '$append' => [
                        '$email' => $email,
                    ],
                ],
            ],
        ];

        return $this->sendRequest(
            'event',
            json_encode($payload)
        );
    }

    protected function sendRequest(string $uri, string $payload): ResponseInterface
    {
        $date = gmdate('D, d M Y H:i:s T');

        return $this->httpClient->post($uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Date' => $date,
                'Authorization' => "Bearer {$this->apiKey}",
            ],
            'body' => $payload,
        ]);
    }
}
