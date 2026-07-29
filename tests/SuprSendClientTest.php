<?php

namespace NotificationChannels\SuprSend\Test;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use NotificationChannels\SuprSend\SuprSendClient;
use NotificationChannels\SuprSend\SuprSendMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(SuprSendClient::class)]
class SuprSendClientTest extends TestCase
{
    #[Test]
    public function it_points_at_the_suprsend_hub(): void
    {
        $this->assertSame('https://hub.suprsend.com/', SuprSendClient::API_URL);
    }

    #[Test]
    public function it_falls_back_to_a_real_guzzle_client_when_none_is_injected(): void
    {
        $client = new SuprSendClient();

        $this->assertInstanceOf(SuprSendClient::class, $client);
    }

    #[Test]
    public function trigger_event_posts_to_the_event_endpoint(): void
    {
        $this->fakeClient(new Response(200))->triggerEvent(
            SuprSendMessage::create()->eventName('invoice_paid'),
            'distinct-id'
        );

        $request = $this->recordedRequest(0);

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://hub.suprsend.com/event', (string) $request->getUri());
    }

    #[Test]
    public function trigger_event_sends_the_distinct_id_event_name_and_properties(): void
    {
        $message = SuprSendMessage::create()
            ->eventName('invoice_paid')
            ->data(['invoice_number' => 'INV-1', 'amount' => 100]);

        $this->fakeClient(new Response(200))->triggerEvent($message, 'distinct-id');

        $this->assertSame([
            'distinct_id' => 'distinct-id',
            'event' => 'invoice_paid',
            'properties' => ['invoice_number' => 'INV-1', 'amount' => 100],
        ], $this->recordedBody(0));
    }

    #[Test]
    public function trigger_event_sends_an_empty_properties_object_when_there_is_no_data(): void
    {
        $this->fakeClient(new Response(200))->triggerEvent(
            SuprSendMessage::create()->eventName('ping'),
            'distinct-id'
        );

        $this->assertSame([], $this->recordedBody(0)['properties']);
    }

    #[Test]
    public function append_user_sends_the_schema_two_user_operations_payload(): void
    {
        $this->fakeClient(new Response(200))->appendUser('taylor@laravel.com', 'distinct-id');

        $this->assertSame([
            '$schema' => 2,
            'distinct_id' => 'distinct-id',
            '$user_operations' => [
                ['$append' => ['$email' => 'taylor@laravel.com']],
            ],
        ], $this->recordedBody(0));
    }

    #[Test]
    public function append_user_posts_to_the_same_event_endpoint(): void
    {
        $this->fakeClient(new Response(200))->appendUser('taylor@laravel.com', 'distinct-id');

        $this->assertSame('https://hub.suprsend.com/event', (string) $this->recordedRequest(0)->getUri());
    }

    #[Test]
    public function it_authenticates_with_the_configured_api_key(): void
    {
        $this->fakeClient(new Response(200))->appendUser('taylor@laravel.com', 'distinct-id');

        $this->assertSame(
            'Bearer '.self::API_KEY,
            $this->recordedRequest(0)->getHeaderLine('Authorization')
        );
    }

    #[Test]
    public function it_reads_the_api_key_from_the_services_config_at_construction_time(): void
    {
        config()->set('services.suprSend.api_key', 'rotated-key');

        $this->fakeClient(new Response(200))->appendUser('taylor@laravel.com', 'distinct-id');

        $this->assertSame('Bearer rotated-key', $this->recordedRequest(0)->getHeaderLine('Authorization'));
    }

    #[Test]
    public function it_sends_a_json_content_type(): void
    {
        $this->fakeClient(new Response(200))->appendUser('taylor@laravel.com', 'distinct-id');

        $this->assertSame('application/json', $this->recordedRequest(0)->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function it_sends_an_rfc_formatted_gmt_date_header(): void
    {
        $this->fakeClient(new Response(200))->appendUser('taylor@laravel.com', 'distinct-id');

        $this->assertMatchesRegularExpression(
            '/^[A-Z][a-z]{2}, \d{2} [A-Z][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} GMT$/',
            $this->recordedRequest(0)->getHeaderLine('Date')
        );
    }

    #[Test]
    public function trigger_event_returns_the_response(): void
    {
        $response = $this->fakeClient(new Response(202))->triggerEvent(
            SuprSendMessage::create()->eventName('invoice_paid'),
            'distinct-id'
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(202, $response->getStatusCode());
    }

    #[Test]
    public function append_user_returns_the_response(): void
    {
        $response = $this->fakeClient(new Response(202))->appendUser('taylor@laravel.com', 'distinct-id');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(202, $response->getStatusCode());
    }

    #[Test]
    public function guzzle_errors_are_not_swallowed_by_the_client(): void
    {
        $client = $this->fakeClient(new ServerException(
            'Server error: 500',
            new Request('POST', 'event'),
            new Response(500)
        ));

        $this->expectException(ServerException::class);

        $client->appendUser('taylor@laravel.com', 'distinct-id');
    }
}
