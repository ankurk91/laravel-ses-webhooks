<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Ankurk91\SESWebhooks\Exception\WebhookFailed;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessSesWebhookJob extends ProcessWebhookJob
{
    public function handle()
    {
        if ($this->webhookCall->payload['Type'] !== 'Notification') {
            return;
        }

        try {
            $event = $this->getEventData();
        } catch (JsonException $e) {
            return;
        }

        if (!Arr::get($event, 'eventType')) {
            return;
        }

        $eventKey = $this->createEventKey($event['eventType']);

        event("ses-webhooks::{$eventKey}", $this->webhookCall);

        $jobClass = config("ses-webhooks.jobs.{$eventKey}");

        if (empty($jobClass)) {
            return;
        }

        if (!class_exists($jobClass)) {
            throw WebhookFailed::jobClassDoesNotExist($jobClass);
        }

        dispatch(new $jobClass($this->webhookCall));
    }

    /**
     * Parse message from payload.
     *
     * @return array
     * @throws JsonException
     */
    protected function getEventData(): array
    {
        return json_decode($this->webhookCall->payload['Message'], true, 512, JSON_THROW_ON_ERROR);
    }

    protected function createEventKey(string $eventType): string
    {
        $key = Str::lower($eventType);
        return Str::replace(' ', '_', $key);
    }
}
