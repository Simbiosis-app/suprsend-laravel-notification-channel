<?php

namespace NotificationChannels\SuprSend;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;

class SuprSendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('suprSend', fn ($app) => $app->make(SuprSendChannel::class));
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }
}
