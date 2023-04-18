<?php

namespace NotificationChannels\SuprSend;

use GuzzleHttp\Client;

class SuprSendClient
{
    const API_URL = 'https://hub.suprsend.com/';

    protected Client $httpClient;
    protected string $workspaceKey;
    protected string $workspaceSecret;
    protected string $apiKey;
    
    public function __construct()
    {
        $this->workspaceKey = config('services.suprSend.workspace_key');
        $this->workspaceSecret = config('services.suprSend.workspace_secret');
        $this->apiKey = config('services.suprSend.api_key');

        $this->httpClient = new Client([
            'base_uri' => self::API_URL,
        ]);

        return $this;
    }

    public function triggerWorkflow(SuprSendMessage $suprSendMessage)
    {
        $this->sendRequest(
            "{$this->workspaceKey}/trigger", 
            json_encode($suprSendMessage->toArray())
        );
    }

    protected function sendRequest($uri, $payload)
    {
        $date = gmdate('D, d M Y H:i:s T');
        $signature = $this->getSignature($uri, $payload, $date);

        return $this->httpClient->post($uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Date' => $date,
                'Authorization' => "Bearer {$this->apiKey}",
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
