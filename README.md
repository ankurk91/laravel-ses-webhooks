# Laravel SES Webhooks Client

[![Packagist](https://badgen.net/packagist/v/ankurk91/laravel-ses-webhooks)](https://packagist.org/packages/ankurk91/laravel-ses-webhooks)
[![GitHub-tag](https://badgen.net/github/tag/ankurk91/laravel-ses-webhooks)](https://github.com/ankurk91/laravel-ses-webhooks/releases)
[![License](https://badgen.net/packagist/license/ankurk91/laravel-ses-webhooks)](LICENSE.txt)
[![Downloads](https://badgen.net/packagist/dt/ankurk91/laravel-ses-webhooks)](https://packagist.org/packages/ankurk91/laravel-ses-webhooks/stats)
[![GH-Actions](https://github.com/ankurk91/laravel-ses-webhooks/workflows/tests/badge.svg)](https://github.com/ankurk91/laravel-ses-webhooks/actions)
[![codecov](https://codecov.io/gh/ankurk91/laravel-ses-webhooks/branch/main/graph/badge.svg)](https://codecov.io/gh/ankurk91/laravel-ses-webhooks)

Handle AWS [SES](https://aws.amazon.com/ses/) webhooks in Laravel php framework.

## Installation

You can install the package via composer:

```bash
composer require "ankurk91/laravel-ses-webhooks"
```

The service provider will automatically register itself.

You must publish the config file with:

```bash
php artisan vendor:publish --provider="Ankurk91\SesWebhooks\SesWebhooksServiceProvider"
```

Next, you must publish the migration with:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
```

After the migration has been published you can create the `webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

Next, for routing, add this line to your `routes/web.php`

```bash
Route::sesWebhooks('/webhooks/ses');
```

Behind the scenes this will register a `POST` route to a controller provided by this package. Next, you must add that
route to the `except` array of your `VerifyCsrfToken` middleware:

```php
protected $except = [
    '/webhooks/*',
];
```

### Prepare your AWS console for SES webhooks

* Assuming that you have SES service up and running in your app already
* Create a [configuration set](https://docs.aws.amazon.com/ses/latest/dg/using-configuration-sets.html)
* Choose [SNS](https://docs.aws.amazon.com/ses/latest/dg/configure-sns-notifications.html) as event destination
* Choose HTTP as delivery method for that newly created SNS topic
* Specify the webhook URL as `${APP_URL}/webhooks/ses`
* This package should auto confirm the subscription
* You need to specify the configuration set name in your `config/services.php`

```php
 'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'options' => [
            'ConfigurationSetName' => env('AWS_SES_CONFIGURATION_SET'),
        ],
    ],
```

## Usage

There are 2 ways to handle incoming webhooks via this package.

### 1 - Handling webhook requests using jobs

If you want to do something when a specific event type comes in; you can define a job for that event. 
Here's an example of such job:

```php
<?php

namespace App\Jobs\Webhooks\SES;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\Models\WebhookCall;

class BounceHandler implements ShouldQueue
{
    use SerializesModels;

    public function __construct(public WebhookCall $webhookCall)
    {
        //
    }

    public function handle()
    {
        $message = $this->webhookCall->payload['Message'];
        
        if (Arr::get($message, 'bounce.bounceType') !== 'Permanent') return;

        foreach ($message['bounce']['bouncedRecipients'] as $recipient) {
            // todo do something with $recipient['emailAddress']
        }
    }
}
```

After having created your job you must register it at the `jobs` array in the `config/ses-webhooks.php` config file. The
key should be lowercase and spaces should be replaced by `_`. The value should be a fully qualified classname.

```php
<?php

return [
     'jobs' => [
          'bounce' => \App\Jobs\Webhooks\SES\BounceHandler::class,
     ],
];
```

### 2 - Handling webhook requests using events and listeners

Instead of queueing jobs to perform some work when a webhook request comes in, you can opt to listen to the events this
package will fire. Whenever a valid request hits your app, the package will fire a `ses-webhooks::<name-of-the-event>`
event.

The payload of the events will be the instance of WebhookCall that was created for the incoming request.

You can listen for such event by registering the listener in your `EventServiceProvider` class.

```php
protected $listen = [
    'ses-webhooks::complaint' => [
        App\Listeners\SES\ComplaintListener::class,
    ],
];
```

Here's an example of such listener class:

```php
<?php

namespace App\Listeners\SES;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;

class ComplaintListener implements ShouldQueue
{
    public function handle(WebhookCall $webhookCall)
    {
        $message = $webhookCall->payload['Message'];
        
        foreach ($message['complaint']['complainedRecipients'] as $recipient) {
            // todo do something with $recipient['emailAddress']
        }
    }
}
```

## Pruning old webhooks (opt-in)

You can schedule the artisan command to remove old webhooks from database like:

```php
<?php
namespace App\Console;

use Ankurk91\SesWebhooks\Model\SesWebhookCall;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(PruneCommand::class, [
            '--model' => [SesWebhookCall::class]
        ])
        ->onOneServer()
        ->daily()
        ->description('Prune webhook_calls for SES.');
    }
    
    //...
}
```

By default, the package is configured to keep past `30` days records only. You can adjust the duration
in `config/ses-webhooks.php` file.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Testing

```bash
composer test
```

### Security

If you discover any security issue, please email `pro.ankurk1[at]gmail[dot]com` instead of using the issue tracker.

### Useful Links

* [Receiving Amazon SES notifications using Amazon SNS](https://docs.aws.amazon.com/ses/latest/dg/monitor-sending-activity-using-notifications-sns.html)
* [AWS SES event publishing](https://docs.aws.amazon.com/ses/latest/dg/monitor-sending-using-event-publishing-setup.html)
* [AWS SNS JSON format](https://docs.aws.amazon.com/sns/latest/dg/sns-message-and-json-formats.html)
* [AWS SES Event format](https://docs.aws.amazon.com/ses/latest/dg/event-publishing-retrieving-sns-contents.html)

### Acknowledgment

This package is highly inspired by:

* https://github.com/spatie/laravel-mailcoach-ses-feedback
* https://github.com/spatie/laravel-stripe-webhooks

### License

This package is licensed under [MIT License](https://opensource.org/licenses/MIT).
