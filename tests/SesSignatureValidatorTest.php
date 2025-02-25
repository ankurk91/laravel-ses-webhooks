<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Tests;

use Ankurk91\SesWebhooks\Http\Controllers\SesWebhooksController;
use Ankurk91\SesWebhooks\Jobs\ProcessSesWebhookJob;
use Ankurk91\SesWebhooks\Model\SesWebhookCall;
use Ankurk91\SesWebhooks\SesSignatureValidator;
use Ankurk91\SesWebhooks\SesWebhookConfig;
use Ankurk91\SesWebhooks\SesWebhookProfile;
use Ankurk91\SesWebhooks\SesWebhooksServiceProvider;
use Ankurk91\SesWebhooks\Tests\Factory\SNSMessageFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\WebhookClient\WebhookConfig;

#[CoversClass(SesWebhooksController::class)]
#[CoversClass(ProcessSesWebhookJob::class)]
#[CoversClass(SesWebhookCall::class)]
#[CoversClass(SesSignatureValidator::class)]
#[CoversClass(SesWebhookConfig::class)]
#[CoversClass(SesWebhookProfile::class)]
#[CoversClass(SesWebhooksServiceProvider::class)]
class SesSignatureValidatorTest extends TestCase
{
    private readonly WebhookConfig $config;
    private readonly SesSignatureValidator $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = SesWebhookConfig::get();
        $this->validator = new SesSignatureValidator();
    }

    public function test_it_passes_for_valid_payload()
    {
        $messageFactory = new SNSMessageFactory();
        $payload = $messageFactory->getNotificationPayload();
        $request = Request::create('/webhooks/ses', 'POST', [], [], [], [], json_encode($payload));

        Http::fake([
            'amazonaws.com/*' => Http::response($messageFactory->getCertificate())
        ]);

        $this->assertTrue($this->validator->isValid($request, $this->config));
    }

    public function test_it_fails_for_invalid_payload()
    {
        Http::fake();
        $payload = ['MessageId' => 123]; // incomplete message
        $request = Request::create('/webhooks/ses', 'POST', [], [], [], [], json_encode($payload));

        $this->assertFalse($this->validator->isValid($request, $this->config));
    }

    public function test_it_calls_the_subscribe_url_for_subscription_confirmation_event()
    {
        $messageFactory = new SNSMessageFactory();
        $payload = $messageFactory->getSubscriptionConfirmationPayload();
        $request = Request::create('/webhooks/ses', 'POST', [], [], [], [], json_encode($payload));

        Http::fake([
            'amazonaws.com/*' => Http::response($messageFactory->getCertificate()),
            'localhost/*' => Http::response('ok.'),
        ]);
        $this->assertTrue($this->validator->isValid($request, $this->config));

        Http::assertSent(function ($request) use ($payload) {
            return $request->url() == $payload['SubscribeURL'];
        });
    }
}
