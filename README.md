# SuprSend Notification Channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/suprsend.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/suprsend)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/github/actions/workflow/status/laravel-notification-channels/suprsend/tests.yml?branch=master&style=flat-square)](https://github.com/laravel-notification-channels/suprsend/actions/workflows/tests.yml)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/:sensio_labs_id.svg?style=flat-square)](https://insight.sensiolabs.com/projects/:sensio_labs_id)
[![Quality Score](https://img.shields.io/scrutinizer/g/laravel-notification-channels/suprsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/suprsend)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/laravel-notification-channels/suprsend/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/suprsend/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/suprsend.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/suprsend)

This package makes it easy to send notifications using [SuprSend](https://www.suprsend.com/) with Laravel 12.x and 13.x

## Requirements

- PHP 8.3 – 8.5
- Laravel 12.x or 13.x

> Need Laravel 11 or older? Use the `1.x` releases of this package.

SuprSend is a notification channel that can be used with Laravel. With SuprSend, Laravel developers can easily send notifications to their users via various channels, including email, SMS, and push notifications.

To get started with SuprSend, developers can install the package via Composer and configure their SuprSend API credentials in the Laravel configuration file. Once set up, developers can use Laravel's built-in notification system to send notifications via SuprSend.

## Contents

- [Installation](#installation)
	- [Setting up the SuprSend service](#setting-up-the-SuprSend-service)
- [Usage](#usage)
	- [Available Message methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

You can install the SuprSend notification channel for Laravel using Composer. Run the following command in your terminal:

```bash
composer require laravel-notification-channels/suprsend
```

The service provider is registered automatically via package auto-discovery — no manual
registration is required. If you have disabled auto-discovery, add it to
`bootstrap/providers.php` yourself:

```php
return [
    // Other service providers...

    NotificationChannels\SuprSend\SuprSendServiceProvider::class,
];
```

Finally, configure your SuprSend API credentials in your config/services.php file:

```php
'suprSend' => [
    'workspace_key' => env('SUPRSEND_WORKSPACE_KEY'),
    'workspace_secret' => env('SUPRSEND_WORKSPACE_SECRET'),
    'api_key' => env('SUPRSEND_API_KEY'),
],
```

## Usage

To send notifications via SuprSend using the SuprSendMessage class, you first need to create a Laravel notification class. In your notification class, define a toSuprSend method that returns an instance of the SuprSendMessage class:


```php
use Illuminate\Notifications\Notification;
use NotificationChannels\SuprSend\SuprSendMessage;

class InvoicePaid extends Notification
{
    public function via($notifiable)
    {
        return ['suprSend'];
    }

    public function toSuprSend($notifiable)
    {
        return SuprSendMessage::create()
            ->eventName('invoice_paid')
            ->data([
                'invoice_number' => $this->invoice->number,
                'amount' => $this->invoice->amount,
            ]);
    }
}
```

In the `toSuprSend` method, you can customize the `SuprSendMessage` instance to your needs. Set the `eventName` property to specify the name of the event you want to trigger in SuprSend. Use the `data` method to pass any additional data to the notification.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email marcorivm@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Marco Rivadeneyra](https://github.com/marcorivm)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
