<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Tests\Stubs;

use Spatie\WebhookClient\Models\WebhookCall;

class TestEventJob
{
    public function __construct(public WebhookCall $webhookCall)
    {
        //
    }

    public function handle()
    {
        //
    }
}
