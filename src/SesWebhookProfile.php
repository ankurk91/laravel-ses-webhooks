<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Aws\Sns\Message;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;

class SesWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        $payload = Message::fromJsonString((string)$request->getContent());

        return SesWebhookCall::query()
            ->where('payload->MessageId', $payload['MessageId'])->doesntExist();
    }
}
