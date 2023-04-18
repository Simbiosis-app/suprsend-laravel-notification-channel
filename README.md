# SuprSend Notification Channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/suprsend.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/suprsend)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/laravel-notification-channels/suprsend/master.svg?style=flat-square)](https://travis-ci.org/laravel-notification-channels/suprsend)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/:sensio_labs_id.svg?style=flat-square)](https://insight.sensiolabs.com/projects/:sensio_labs_id)
[![Quality Score](https://img.shields.io/scrutinizer/g/laravel-notification-channels/suprsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/suprsend)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/laravel-notification-channels/suprsend/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/suprsend/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/suprsend.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/suprsend)

This package makes it easy to send notifications using [SuprSend](https://www.suprsend.com/) with Laravel 5.5+, 6.x, 7.x, 8.x and 9.x

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

Next, add the SuprSend service provider to your config/app.php file:

```php
'providers' => [
    // Other service providers...

    NotificationChannels\SuprSend\SuprSendServiceProvider::class,
],
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

To send notifications via SuprSend using the `SuprSendMessage` class, you first need to create a Laravel notification class. In your notification class, define a `toSuprSend` method that returns an instance of the SuprSendMessage class:

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
            ->workflowName('invoice_paid')
            ->template('your_invoice_has_been_paid')
            ->data([
                'invoice_number' => $this->invoice->number,
                'amount' => $this->invoice->amount,
            ])
            ->notificationCategory(NotificationCategory::Promotional)
            ->delay('15m')
            ->attachments([
                [
                    'filename' => 'billing.pdf',
                    'contentType' => 'application/pdf',
                    'data' => 'Q29uZ3JhdHVsYXRpb25zLCB5b3UgY2FuIGJhc2U2NCBkZWNvZGUh'
                ],
            ]);
    }
}

```

In the toSuprSend method, you can customize the `SuprSendMessage` instance to your needs. Set the `workflowName` and `template` properties to specify the SuprSend workflow and template to use. Use the `data` method to pass any additional data to the notification. Use the `notificationCategory` method to specify the notification category. Use the `delay` method to specify a delay before sending the notification. And use the `attachments` method to attach files to the notification.

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
