<?php

namespace Tests;

use Laravel\Socialite\SocialiteServiceProvider;
use Revolution\Socialite\Discord\DiscordServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SocialiteServiceProvider::class,
            DiscordServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            //
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set(
            'services.discord',
            [
                'client_id'     => 'test',
                'client_secret' => 'test',
                'redirect'      => 'http://localhost',
            ]
        );
    }
}
