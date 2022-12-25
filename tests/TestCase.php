<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Tests;

use Ankurk91\SesWebhooks\SesWebhooksServiceProvider;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Route::sesWebhooks('/webhooks/ses');

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SesWebhooksServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('app.debug', false);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $migration = include __DIR__ . '/../vendor/spatie/laravel-webhook-client/database/migrations/create_webhook_calls_table.php.stub';

        $migration->up();
    }

}
