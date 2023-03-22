<?php

namespace NotificationChannels\SuprSend;

use GuzzleHttp\Client;

class SuprSendClient
{
    const API_URL = 'https://hub.suprsend.com/';

    public function __construct(protected string $workspaceKey, protected string $workspaceSecret, protected array $config, protected Client $httpClient)
    {
        $httpClient->setDefaultOption('base_url', self::API_URL);
        return $this;
    }

    public function createOrUpdate($userId, $channels)
    {
    }

    public function sendEvent($payload)
    {
        $uri = 'events';
        $response = $this->sendRequest($uri, $payload);
        return $response;
    }

    public function sendRequest($uri, $payload)
    {
        $date = gmdate('D, d M Y H:i:s T');
        $signature = $this->getSignature($uri, $payload, $date);
        return $this->httpClient->post($uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Date' => $date,
                'Authorization' => "{$this->workspaceKey}:{$signature}",
            ],
            'body' => $payload,
        ]);
    }

    public function getSignature($resource, $payload, $date, $method = 'POST', $contentType = 'application/json')
    {
        $uri = '/' . trim($resource, '/') . '/';
        $contentMD5 = md5($payload);
        $stringToSign = $method . "\n" . $contentMD5 . "\n" . $contentType . "\n" . $date . "\n" . $uri;
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $this->workspaceSecret, true));

        return $signature;
    }
}
