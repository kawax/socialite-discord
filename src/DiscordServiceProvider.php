<?php

namespace Revolution\Socialite\Discord;

use Laravel\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class DiscordServiceProvider extends SocialiteServiceProvider
{
    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        Socialite::extend('discord', function ($app) {
            $config = $app['config']['services.discord'];

            return Socialite::buildProvider(DiscordProvider::class, $config);
        });
    }
}
