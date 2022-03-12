<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Aws\Sns\Message;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use JsonException;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class SesWebhookCall extends WebhookCall
{
    use MassPrunable;

    protected $table = 'webhook_calls';

    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $payload = self::makePayload($request);
        $headers = self::headersToStore($config, $request);

        return self::create([
            'name' => $config->name,
            'url' => $request->fullUrl(),
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

    public function prunable()
    {
        $config = SesWebhookConfig::get();
        $hours = config('ses-webhooks.prune_older_than_hours', 30 * 24);

        return static::query()
            ->where('name', $config->name)
            ->where('created_at', '<', Date::now()->subHours($hours));
    }
}
