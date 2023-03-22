<?php

namespace NotificationChannels\SuprSend;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class SuprSendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Bootstrap code here.

        $this->app->when(SuprSendChannel::class)
            ->needs(SuprSendClient::class)
            ->give(function () {
                $workspaceKey = config('services.suprsend.workspace_key');
                $workspaceSecret = config('services.suprsend.workspace_secret');
                $config = config('services.suprsend.config', []);

                return new SuprSendClient($workspaceKey, $workspaceSecret, $config, new Client());
            });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
