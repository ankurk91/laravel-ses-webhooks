<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Model;

use Aws\Sns\Message;
use Illuminate\Http\Request;
use JsonException;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class SesWebhookCall extends WebhookCall
{
    protected $table = 'webhook_calls';

    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $payload = self::makePayload($request);
        $headers = self::headersToStore($config, $request);

        return self::create([
            'name' => $config->name,
            'exception' => null,
            'url' => $request->path(),
            'headers' => $headers,
            'payload' => $payload,
        ]);
    }

    protected static function makePayload(Request $request): array
    {
        $payload = Message::fromJsonString((string)$request->getContent())->toArray();

        try {
            $message = json_decode($payload['Message'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $message = $payload['Message'];
        }

        $payload['Message'] = $message;

        return $payload;
    }
}
