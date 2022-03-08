<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Tests;

use Ankurk91\SesWebhooks\SesWebhookCall;
use Ankurk91\SesWebhooks\SesWebhookConfig;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class SesWebhookPruneTest extends TestCase
{
    use RefreshDatabase;

    protected function createWebhook(Carbon $createdAt): SesWebhookCall
    {
        $config = SesWebhookConfig::get();

        $webhook = new SesWebhookCall();
        $webhook->forceFill([
            'name' => $config->name,
            'url' => '',
            'headers' => [],
            'payload' => [],
            'created_at' => $createdAt
        ]);

        $webhook->save();
        return $webhook;
    }

    public function test_it_prune_old_records()
    {
        $hours = config('ses-webhooks.prune_older_than_hours') + 1;
        $oldWebhook = $this->createWebhook(Date::now()->subHours($hours));
        $newWebhook = $this->createWebhook(Date::now());

        $this->artisan(PruneCommand::class, [
            '--model' => [SesWebhookCall::class]
        ]);

        $this->assertDatabaseMissing(SesWebhookCall::class, [
            'id' => $oldWebhook->getKey(),
        ]);

        $this->assertDatabaseHas(SesWebhookCall::class, [
            'id' => $newWebhook->getKey(),
        ]);
    }
}
