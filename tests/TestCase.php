<?php

namespace Tests;

use Laravel\Socialite\SocialiteServiceProvider;
use Revolution\Socialite\Discord\DiscordServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SocialiteServiceProvider::class,
            DiscordServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            //
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('services.discord',
            [
                'client_id' => 'test',
                'client_secret' => 'test',
                'redirect' => 'http://localhost',
            ]
        );
    }
}
