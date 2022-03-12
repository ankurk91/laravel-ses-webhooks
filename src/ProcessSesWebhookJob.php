<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Ankurk91\SesWebhooks\Exception\WebhookFailed;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessSesWebhookJob extends ProcessWebhookJob
{
    public function handle()
    {
        if ($this->webhookCall->payload['Type'] !== 'Notification') {
            return;
        }

        $message = $this->webhookCall->payload['Message'];

        if (!Arr::get($message, 'eventType')) {
            return;
        }

        $eventKey = $this->createEventKey($message['eventType']);

        event("ses-webhooks::$eventKey", $this->webhookCall);

        $jobClass = config("ses-webhooks.jobs.$eventKey");

        if (empty($jobClass)) {
            return;
        }

        if (!class_exists($jobClass)) {
            throw WebhookFailed::jobClassDoesNotExist($jobClass);
        }

        dispatch(new $jobClass($this->webhookCall));
    }

    protected function createEventKey(string $eventType): string
    {
        $key = Str::lower($eventType);
        return Str::replace(' ', '_', $key);
    }
}
