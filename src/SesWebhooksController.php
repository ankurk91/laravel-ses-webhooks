<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProcessor;

class SesWebhooksController
{
    public function __invoke(Request $request)
    {
        $webhookConfig = SesWebhookConfig::get();

        return (new WebhookProcessor($request, $webhookConfig))->process();
    }
}
