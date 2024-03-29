<?php

namespace Revolution\Socialite\Discord;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class DiscordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        Socialite::extend('discord', function ($app) {
            return Socialite::buildProvider(DiscordProvider::class, $app['config']['services.discord']);
        });
    }
}
