<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SesWebhooksServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigPath() => config_path('ses-webhooks.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'ses-webhooks');

        Route::macro('sesWebhooks', function (string $url) {
            return Route::post($url, '\Ankurk91\SesWebhooks\SesWebhooksController');
        });
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../config/ses-webhooks.php';
    }
}
