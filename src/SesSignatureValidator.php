<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;
use Throwable;

class SesSignatureValidator implements SignatureValidator
{
    /**
     * @throws RequestException
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (!config('ses-webhooks.verify_signature')) {
            return true;
        }

        try {
            $message = Message::fromJsonString((string)$request->getContent());
            (new MessageValidator($this->makeCertClient()))->validate($message);

            if ($message['Type'] === 'SubscriptionConfirmation') {
                $this->confirmSubscription($message);
            }

            return true;
        } catch (RequestException $e) {
            throw $e;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Use Guzzle HTTP Client instead of file_get_contents()
     */
    protected function makeCertClient(): callable
    {
        return function (string $url): string {
            return Http::get($url)->body();
        };
    }

    /**
     * Sends a GET request to confirmation URL.
     *
     * @throws RequestException
     */
    protected function confirmSubscription(Message $message): void
    {
        Http::get($message['SubscribeURL'])->throw();
    }
}
