<?php

namespace NotificationChannels\SuprSend\Test;

use NotificationChannels\SuprSend\SuprSendMessage;
use NotificationChannels\SuprSend\Test\Fixtures\TestNotifiable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase as BaseTestCase;

#[CoversClass(SuprSendMessage::class)]
class SuprSendMessageTest extends BaseTestCase
{
    #[Test]
    public function it_creates_an_instance_through_the_static_factory(): void
    {
        $this->assertInstanceOf(SuprSendMessage::class, SuprSendMessage::create());
    }

    #[Test]
    public function the_factory_is_late_static_bound_so_subclasses_stay_subclasses(): void
    {
        $message = ExtendedSuprSendMessage::create();

        $this->assertInstanceOf(ExtendedSuprSendMessage::class, $message);
    }

    #[Test]
    public function the_fluent_setters_return_the_same_instance(): void
    {
        $message = SuprSendMessage::create();

        $this->assertSame($message, $message->eventName('invoice_paid'));
        $this->assertSame($message, $message->data(['a' => 1]));
        $this->assertSame($message, $message->notifiable(new TestNotifiable()));
    }

    #[Test]
    public function it_stores_the_event_name(): void
    {
        $message = SuprSendMessage::create()->eventName('invoice_paid');

        $this->assertSame('invoice_paid', $message->getEventName());
    }

    #[Test]
    public function the_last_event_name_wins(): void
    {
        $message = SuprSendMessage::create()
            ->eventName('first')
            ->eventName('second');

        $this->assertSame('second', $message->getEventName());
    }

    #[Test]
    public function the_data_payload_defaults_to_an_empty_array(): void
    {
        $this->assertSame([], SuprSendMessage::create()->getData());
    }

    #[Test]
    public function it_stores_the_data_payload(): void
    {
        $data = ['invoice_number' => 'INV-1', 'amount' => 100];

        $this->assertSame($data, SuprSendMessage::create()->data($data)->getData());
    }

    #[Test]
    public function calling_data_replaces_the_previous_payload_rather_than_merging(): void
    {
        $message = SuprSendMessage::create()
            ->data(['a' => 1])
            ->data(['b' => 2]);

        $this->assertSame(['b' => 2], $message->getData());
    }

    #[Test]
    public function it_stores_the_notifiable_under_the_notifiable_key(): void
    {
        $notifiable = new TestNotifiable();

        $message = SuprSendMessage::create()->notifiable($notifiable);

        $this->assertSame($notifiable, $message->getData()['notifiable']);
    }

    #[Test]
    public function attaching_a_notifiable_preserves_the_existing_data(): void
    {
        $message = SuprSendMessage::create()
            ->data(['invoice_number' => 'INV-1'])
            ->notifiable(new TestNotifiable());

        $this->assertSame('INV-1', $message->getData()['invoice_number']);
        $this->assertArrayHasKey('notifiable', $message->getData());
    }

    #[Test]
    public function calling_data_after_attaching_a_notifiable_drops_the_notifiable(): void
    {
        $message = SuprSendMessage::create()
            ->notifiable(new TestNotifiable())
            ->data(['invoice_number' => 'INV-1']);

        $this->assertArrayNotHasKey('notifiable', $message->getData());
    }

    #[Test]
    public function reading_the_event_name_before_setting_it_fails_loudly(): void
    {
        $this->expectException(\Error::class);

        SuprSendMessage::create()->getEventName();
    }
}

class ExtendedSuprSendMessage extends SuprSendMessage
{
}
