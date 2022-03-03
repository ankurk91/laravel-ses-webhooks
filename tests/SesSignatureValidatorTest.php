<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Tests;

use Ankurk91\SesWebhooks\SesSignatureValidator;
use Ankurk91\SesWebhooks\SesWebhookConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookClient\WebhookConfig;

class SesSignatureValidatorTest extends TestCase
{
    private WebhookConfig $config;
    private SesSignatureValidator $validator;

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
