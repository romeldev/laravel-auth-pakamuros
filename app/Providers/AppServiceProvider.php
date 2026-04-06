<?php

namespace App\Providers;

use App\Socialite\PakamurosProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Socialite::extend('pakamuros', function ($app) {
            $config = $app['config']['services.pakamuros'];

            return new PakamurosProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });
    }
}
