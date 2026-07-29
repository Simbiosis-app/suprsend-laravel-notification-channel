<?php

namespace NotificationChannels\SuprSend\Test;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\SuprSend\Exceptions\CouldNotSendNotification;
use NotificationChannels\SuprSend\SuprSendChannel;
use NotificationChannels\SuprSend\SuprSendClient;
use NotificationChannels\SuprSend\Test\Fixtures\TestNotifiable;
use NotificationChannels\SuprSend\Test\Fixtures\TestNotification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SuprSendChannel::class)]
class SuprSendChannelTest extends TestCase
{
    /**
     * hash('sha256', 'taylor@laravel.com'.'suprsend')
     */
    public const DISTINCT_ID = 'b83963612c2d7b109d0bf719ad235b5f256e8b222e9ecb1ebc65ca09cb45696e';

    private function channel(mixed ...$responses): SuprSendChannel
    {
        return new SuprSendChannel($this->fakeClient(...$responses));
    }

    private function ok(): array
    {
        return [new Response(200), new Response(200)];
    }

    #[Test]
    public function it_builds_its_own_client_when_none_is_injected(): void
    {
        $this->assertInstanceOf(SuprSendChannel::class, new SuprSendChannel());
    }

    #[Test]
    public function it_appends_the_user_before_triggering_the_event(): void
    {
        $this->channel(...$this->ok())->send(new TestNotifiable(), new TestNotification());

        $this->assertRequestCount(2);
        $this->assertArrayHasKey('$user_operations', $this->recordedBody(0));
        $this->assertArrayHasKey('event', $this->recordedBody(1));
    }

    #[Test]
    public function it_appends_the_notifiable_email_to_the_suprsend_user(): void
    {
        $this->channel(...$this->ok())->send(
            new TestNotifiable('taylor@laravel.com'),
            new TestNotification()
        );

        $this->assertSame(
            [['$append' => ['$email' => 'taylor@laravel.com']]],
            $this->recordedBody(0)['$user_operations']
        );
    }

    #[Test]
    public function it_derives_the_distinct_id_from_the_notifiable_email(): void
    {
        $this->channel(...$this->ok())->send(new TestNotifiable(), new TestNotification());

        $this->assertSame(self::DISTINCT_ID, $this->recordedBody(0)['distinct_id']);
        $this->assertSame(self::DISTINCT_ID, $this->recordedBody(1)['distinct_id']);
    }

    #[Test]
    public function both_requests_use_the_same_distinct_id(): void
    {
        $this->channel(...$this->ok())->send(new TestNotifiable(), new TestNotification());

        $this->assertSame(
            $this->recordedBody(0)['distinct_id'],
            $this->recordedBody(1)['distinct_id']
        );
    }

    #[Test]
    #[DataProvider('equivalentEmails')]
    public function the_distinct_id_is_normalised_for_case_and_surrounding_whitespace(string $email): void
    {
        $this->channel(...$this->ok())->send(new TestNotifiable($email), new TestNotification());

        $this->assertSame(self::DISTINCT_ID, $this->recordedBody(1)['distinct_id']);
    }

    public static function equivalentEmails(): array
    {
        return [
            'as given' => ['taylor@laravel.com'],
            'upper case' => ['TAYLOR@LARAVEL.COM'],
            'mixed case' => ['Taylor@Laravel.Com'],
            'leading whitespace' => ['   taylor@laravel.com'],
            'trailing whitespace' => ['taylor@laravel.com   '],
            'surrounding whitespace' => ["\t taylor@laravel.com \n"],
        ];
    }

    #[Test]
    public function different_emails_produce_different_distinct_ids(): void
    {
        $this->channel(...$this->ok())->send(new TestNotifiable('a@laravel.com'), new TestNotification());
        $this->channel(...$this->ok())->send(new TestNotifiable('b@laravel.com'), new TestNotification());

        $this->assertNotSame(
            $this->recordedBody(0)['distinct_id'],
            $this->recordedBody(2)['distinct_id']
        );
    }

    #[Test]
    public function it_forwards_the_event_name_from_the_message(): void
    {
        $this->channel(...$this->ok())->send(
            new TestNotifiable(),
            new TestNotification('subscription_renewed')
        );

        $this->assertSame('subscription_renewed', $this->recordedBody(1)['event']);
    }

    #[Test]
    public function it_forwards_the_message_data_as_event_properties(): void
    {
        $this->channel(...$this->ok())->send(
            new TestNotifiable(),
            new TestNotification('invoice_paid', ['invoice_number' => 'INV-9', 'amount' => 4200])
        );

        $properties = $this->recordedBody(1)['properties'];

        $this->assertSame('INV-9', $properties['invoice_number']);
        $this->assertSame(4200, $properties['amount']);
    }

    #[Test]
    public function it_attaches_the_notifiable_to_the_event_properties(): void
    {
        $this->channel(...$this->ok())->send(
            new TestNotifiable('taylor@laravel.com'),
            new TestNotification()
        );

        $properties = $this->recordedBody(1)['properties'];

        $this->assertArrayHasKey('notifiable', $properties);
        $this->assertSame('taylor@laravel.com', $properties['notifiable']['email']);
    }

    #[Test]
    public function it_wraps_a_failure_while_appending_the_user(): void
    {
        $channel = $this->channel(new ClientException(
            'Client error: 401 Unauthorized',
            new Request('POST', 'event'),
            new Response(401)
        ));

        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Client error: 401 Unauthorized');

        $channel->send(new TestNotifiable(), new TestNotification());
    }

    #[Test]
    public function it_does_not_trigger_the_event_when_appending_the_user_fails(): void
    {
        $channel = $this->channel(new ClientException(
            'Client error: 401 Unauthorized',
            new Request('POST', 'event'),
            new Response(401)
        ));

        try {
            $channel->send(new TestNotifiable(), new TestNotification());
        } catch (CouldNotSendNotification) {
            // expected
        }

        $this->assertRequestCount(1);
    }

    #[Test]
    public function it_wraps_a_failure_while_triggering_the_event(): void
    {
        $channel = $this->channel(
            new Response(200),
            new ConnectException('cURL error 6: Could not resolve host', new Request('POST', 'event'))
        );

        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('cURL error 6: Could not resolve host');

        $channel->send(new TestNotifiable(), new TestNotification());
    }

    #[Test]
    public function it_sends_through_the_laravel_notification_pipeline(): void
    {
        $this->app->instance(SuprSendClient::class, $this->fakeClient(...$this->ok()));

        Notification::send(new TestNotifiable(), new TestNotification());

        $this->assertRequestCount(2);
        $this->assertSame('invoice_paid', $this->recordedBody(1)['event']);
    }

    #[Test]
    public function the_notification_facade_can_fake_the_channel(): void
    {
        Notification::fake();

        Notification::send($notifiable = new TestNotifiable(), new TestNotification());

        Notification::assertSentTo($notifiable, TestNotification::class);
        $this->assertRequestCount(0);
    }
}
