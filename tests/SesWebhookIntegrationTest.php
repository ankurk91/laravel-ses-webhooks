<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Tests;

use Ankurk91\SesWebhooks\Exception\WebhookFailed;
use Ankurk91\SesWebhooks\Tests\Factory\SNSMessageFactory;
use Ankurk91\SesWebhooks\Tests\Stubs\TestEventJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Spatie\WebhookClient\Models\WebhookCall;

class SesWebhookIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('ses-webhooks.verify_signature', false);
        config()->set('ses-webhooks.jobs', [
            'bounce' => TestEventJob::class,
            'click' => 'UnknownJob::class',
        ]);
    }

    public function test_it_can_processes_webhook_successfully()
    {
        Event::fake();
        Bus::fake(TestEventJob::class);

        $messageFactory = new SNSMessageFactory();
        $payload = $messageFactory->getNotificationPayload([
            'eventType' => 'Bounce',
        ]);

        $this->postJson('/webhooks/ses', $payload)
            ->assertSuccessful();

        $this->assertDatabaseCount('webhook_calls', 1);

        Bus::assertDispatched(TestEventJob::class, function ($job) {
            $this->assertInstanceOf(WebhookCall::class, $job->webhookCall);

            return true;
        });

        Event::assertDispatched('ses-webhooks::bounce', function ($event, $eventPayload) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);

            return true;
        });
    }

    public function test_it_fails_when_invalid_job_class_configured()
    {
        Event::fake();
        $messageFactory = new SNSMessageFactory();
        $payload = $messageFactory->getNotificationPayload([
            'eventType' => 'Click',
        ]);

        $this->postJson('/webhooks/ses', $payload)
            ->assertSuccessful();

        Event::assertDispatched('ses-webhooks::click', function ($event, $eventPayload) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);

            return true;
        });

        Event::assertDispatched(JobFailed::class, function ($event) {
            $this->assertInstanceOf(WebhookFailed::class, $event->exception);

            return true;
        });
    }

    public function test_it_process_same_webhook_only_once()
    {
        $messageFactory = new SNSMessageFactory();
        $payload = $messageFactory->getNotificationPayload([
            'eventType' => 'Bounce',
        ]);

        $this->postJson('/webhooks/ses', $payload)
            ->assertSuccessful();

        $this->postJson('/webhooks/ses', $payload)
            ->assertSuccessful();

        $this->assertDatabaseCount('webhook_calls', 1);
    }

    public function test_it_can_skip_processing_on_invalid_message_json()
    {
        Event::fake();
        Bus::fake(TestEventJob::class);

        $messageFactory = new SNSMessageFactory();
        $payload = $messageFactory->getNotificationPayload([
            'eventType' => 'Bounce',
        ], [
            'Message' => 'Invalid json text.'
        ]);

        $this->postJson('/webhooks/ses', $payload)
            ->assertSuccessful();

        Event::assertNotDispatched('ses-webhooks::bounce');
        Bus::assertNotDispatched(TestEventJob::class);
    }
}
