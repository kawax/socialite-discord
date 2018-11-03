<?php

namespace Revolution\Socialite\Discord;

use Laravel\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Facades\Socialite;

class DiscordServiceProvider extends SocialiteServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

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
