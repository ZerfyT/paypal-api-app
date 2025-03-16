<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Paddle\SDK\Client;
use Paddle\SDK\Environment;
use Paddle\SDK\Options;

class PaddleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function () {
            return new Client(env('PADDLE_VENDOR_ID'), new Options(Environment::SANDBOX));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
