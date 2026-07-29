<?php

namespace NotificationChannels\SuprSend\Test;

use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\SuprSend\SuprSendChannel;
use NotificationChannels\SuprSend\SuprSendClient;
use NotificationChannels\SuprSend\SuprSendServiceProvider;
use NotificationChannels\SuprSend\Test\Fixtures\TestNotifiable;
use NotificationChannels\SuprSend\Test\Fixtures\TestNotification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SuprSendServiceProvider::class)]
class SuprSendServiceProviderTest extends TestCase
{
    #[Test]
    public function the_provider_is_registered(): void
    {
        $this->assertArrayHasKey(
            SuprSendServiceProvider::class,
            $this->app->getLoadedProviders()
        );
    }

    #[Test]
    public function it_registers_the_suprsend_driver_on_the_channel_manager(): void
    {
        $this->assertInstanceOf(SuprSendChannel::class, Notification::driver('suprSend'));
    }

    #[Test]
    public function the_driver_is_resolved_through_the_container(): void
    {
        $this->app->instance(SuprSendClient::class, $this->fakeClient(new Response(200), new Response(200)));

        Notification::driver('suprSend')->send(new TestNotifiable(), new TestNotification());

        // The channel used the container-bound client rather than building its own.
        $this->assertRequestCount(2);
    }

    #[Test]
    public function the_channel_manager_caches_the_resolved_driver(): void
    {
        $this->assertSame(
            Notification::driver('suprSend'),
            Notification::driver('suprSend')
        );
    }

    #[Test]
    public function the_driver_name_is_case_sensitive(): void
    {
        // Laravel resolves drivers by exact key, and the README documents `suprSend`.
        $this->expectException(\InvalidArgumentException::class);

        Notification::driver('suprsend');
    }

    /**
     * Laravel 13 changed Manager::extend() to rebind the driver closure via
     * RebindsCallbacksToSelf, throwing RuntimeException if the bind fails.
     *
     * @see https://laravel.com/docs/13.x/upgrade#manager-extend-callback-binding
     */
    #[Test]
    public function the_extend_callback_survives_the_laravel_13_manager_rebinding(): void
    {
        $manager = $this->app->make(ChannelManager::class);

        $captured = null;

        $manager->extend('suprSendProbe', static function (Application $app) use (&$captured) {
            $captured = $app;

            return new SuprSendChannel();
        });

        $channel = $manager->driver('suprSendProbe');

        $this->assertInstanceOf(SuprSendChannel::class, $channel);
        $this->assertSame($this->app, $captured, 'The extend callback should still receive the container.');
    }

    #[Test]
    public function extend_still_receives_the_container_for_non_static_closures(): void
    {
        $manager = $this->app->make(ChannelManager::class);

        $captured = null;

        // A non-static closure is the shape Laravel 13 rebinds to the manager instance.
        $manager->extend('suprSendProbeBound', function (Application $app) use (&$captured) {
            $captured = $app;

            return new SuprSendChannel();
        });

        $manager->driver('suprSendProbeBound');

        $this->assertSame($this->app, $captured);
    }

    #[Test]
    public function the_provider_boots_without_forcing_the_channel_manager_to_resolve(): void
    {
        // Notification::resolved() must defer registration; booting the provider
        // should not eagerly instantiate the ChannelManager.
        $this->refreshApplication();

        $this->assertFalse($this->app->resolved(ChannelManager::class));
    }

    #[Test]
    public function the_package_is_auto_discoverable(): void
    {
        $composer = json_decode(
            file_get_contents(__DIR__.'/../composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertSame(
            [SuprSendServiceProvider::class],
            $composer['extra']['laravel']['providers']
        );
    }
}
