<?php

namespace Revolution\Socialite\Discord;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class DiscordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        Socialite::extend(
            'discord',
            function ($app) {
                $config = $app['config']['services.discord'];

                return Socialite::buildProvider(DiscordProvider::class, $config);
            }
        );
    }
}
