<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Aws\Sns\Message;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class SesWebhookCall extends WebhookCall
{
    use MassPrunable;

    protected $table = 'webhook_calls';

    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $payload = Message::fromJsonString((string)$request->getContent());
        $headers = self::headersToStore($config, $request);

        return self::create([
            'name' => $config->name,
            'url' => $request->fullUrl(),
            'headers' => $headers,
            'payload' => $payload->toArray(),
        ]);
    }

    public function prunable(): Builder
    {
        $config = SesWebhookConfig::get();
        $hours = config('ses-webhooks.prune_older_than_hours', 30 * 24);

        return static::query()
            ->where('name', $config->name)
            ->where('created_at', '<', Date::now()->subHours($hours));
    }
}
