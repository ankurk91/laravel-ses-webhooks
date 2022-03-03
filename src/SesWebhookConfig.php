<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Spatie\WebhookClient\WebhookConfig;

class SesWebhookConfig
{
    public static function get(): WebhookConfig
    {
        return new WebhookConfig([
            'name' => 'ses',
            'signature_validator' => SesSignatureValidator::class,
            'webhook_profile' => config('ses-webhooks.profile'),
            'webhook_model' => config('ses-webhooks.model'),
            'process_webhook_job' => ProcessSesWebhookJob::class,
        ]);
    }
}
