<?php

namespace NotificationChannels\SuprSend;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class SuprSendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Notification::resolved(function (ChannelManager $service) {
            // Declared static so Laravel 13's Manager::extend() rebinding leaves $this
            // unbound rather than binding the closure to the ChannelManager instance.
            $service->extend('suprSend', static fn (Application $app) => $app->make(SuprSendChannel::class));
        });
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        //
    }
}
