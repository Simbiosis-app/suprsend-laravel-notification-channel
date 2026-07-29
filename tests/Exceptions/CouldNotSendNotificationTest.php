<?php

namespace NotificationChannels\SuprSend\Test\Exceptions;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use NotificationChannels\SuprSend\Exceptions\CouldNotSendNotification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CouldNotSendNotification::class)]
class CouldNotSendNotificationTest extends TestCase
{
    #[Test]
    public function it_is_an_exception(): void
    {
        $exception = CouldNotSendNotification::serviceRespondedWithAnError(
            new \RuntimeException('boom')
        );

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(CouldNotSendNotification::class, $exception);
    }

    #[Test]
    public function it_carries_the_message_of_the_underlying_exception(): void
    {
        $exception = CouldNotSendNotification::serviceRespondedWithAnError(
            new \RuntimeException('SuprSend rejected the request')
        );

        $this->assertSame('SuprSend rejected the request', $exception->getMessage());
    }

    #[Test]
    public function it_carries_the_message_of_a_guzzle_transport_exception(): void
    {
        $guzzleException = new ConnectException(
            'cURL error 6: Could not resolve host: hub.suprsend.com',
            new Request('POST', 'event')
        );

        $exception = CouldNotSendNotification::serviceRespondedWithAnError($guzzleException);

        $this->assertSame(
            'cURL error 6: Could not resolve host: hub.suprsend.com',
            $exception->getMessage()
        );
    }

    #[Test]
    public function it_is_late_static_bound_so_subclasses_stay_subclasses(): void
    {
        $exception = ExtendedCouldNotSendNotification::serviceRespondedWithAnError(
            new \RuntimeException('boom')
        );

        $this->assertInstanceOf(ExtendedCouldNotSendNotification::class, $exception);
    }
}

class ExtendedCouldNotSendNotification extends CouldNotSendNotification
{
}
