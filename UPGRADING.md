# Upgrading

## From 3.x to 4.x

Model prune feature has been removed from `SesWebhookCall` model class.
To restore this feature follow these steps:

* Create a file at `config/webhook-client.php` in your project with this content:

```php
return [   
    'delete_after_days' => 30,
];
```

* Then update your `app/Console/Kernel.php` file like:

```diff
- use Ankurk91\SesWebhooks\Model\SesWebhookCall;
+ use Spatie\WebhookClient\Models\WebhookCall;

$schedule->command(PruneCommand::class, [
-            '--model' => [SesWebhookCall::class]
+            '--model' => [WebhookCall::class]
        ])
        ->onOneServer()
        ->daily()
        ->description('Prune webhook_calls.');
```

## From 2.x to 3.x

Update your class imports as follows:

```diff
- use Ankurk91\SesWebhooks\SesWebhookCall;
+ use Ankurk91\SesWebhooks\Model\SesWebhookCall;
```

## From 1.x to 2.x

Starting from v2.0, the package will decode and store the `Message` property and save along with payload in database.

If you have been decoding the `Message` string in your app, you will no longer need to.

```diff
- $message = json_decode($this->webhookCall->payload['Message'], true, 512, JSON_THROW_ON_ERROR);
+ $message = $this->webhookCall->payload['Message']
```
