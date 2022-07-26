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
     *
     * @return callable
     */
    protected function makeCertClient(): callable
    {
        return function (string $url): string {
            return Http::timeout(30)->get($url)->body();
        };
    }

    /**
     * Sends a GET request to confirmation URL.
     *
     * @param Message $message
     * @return void
     * @throws RequestException
     */
    protected function confirmSubscription(Message $message): void
    {
        Http::timeout(30)->get($message['SubscribeURL'])->throw();
    }
}
