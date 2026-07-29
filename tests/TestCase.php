<?php

namespace NotificationChannels\SuprSend\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use NotificationChannels\SuprSend\SuprSendClient;
use NotificationChannels\SuprSend\SuprSendServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Psr\Http\Message\RequestInterface;

abstract class TestCase extends Orchestra
{
    public const API_KEY = 'test-api-key';

    /**
     * Every request sent through a client built by fakeGuzzle(), in order.
     *
     * @var array<int, array{request: RequestInterface, response: mixed}>
     */
    protected array $requestHistory = [];

    protected function getPackageProviders($app): array
    {
        return [SuprSendServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('services.suprSend', [
            'workspace_key' => 'test-workspace-key',
            'workspace_secret' => 'test-workspace-secret',
            'api_key' => self::API_KEY,
        ]);
    }

    /**
     * Build a Guzzle client that replays the given canned responses (or throws the
     * given exceptions) and records every outgoing request in $this->requestHistory.
     */
    protected function fakeGuzzle(mixed ...$responses): Client
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->requestHistory));

        return new Client([
            'base_uri' => SuprSendClient::API_URL,
            'handler' => $stack,
        ]);
    }

    /**
     * Build a SuprSendClient wired to a fake Guzzle client.
     */
    protected function fakeClient(mixed ...$responses): SuprSendClient
    {
        return new SuprSendClient($this->fakeGuzzle(...$responses));
    }

    protected function recordedRequest(int $index): RequestInterface
    {
        $this->assertArrayHasKey(
            $index,
            $this->requestHistory,
            sprintf('Expected at least %d request(s), %d were sent.', $index + 1, count($this->requestHistory))
        );

        return $this->requestHistory[$index]['request'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function recordedBody(int $index): array
    {
        return json_decode((string) $this->recordedRequest($index)->getBody(), true);
    }

    protected function assertRequestCount(int $expected): void
    {
        $this->assertCount($expected, $this->requestHistory);
    }
}
