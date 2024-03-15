# Socialite for Discord

https://discordapp.com/developers/docs/topics/oauth2

## Requirements
- PHP >= 8.0

> No version restrictions. It may stop working in future versions.

## Installation
```
composer require revolution/socialite-discord
```

### config/services.php

```php
    'discord' => [
        'client_id'     => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect'      => env('DISCORD_REDIRECT'),
    ],
```

### .env
```
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT=
```

## Usage

routes/web.php
```php
Route::get('login', [SocialiteController::class, 'login']);
Route::get('callback', [SocialiteController::class, 'callback']);
```

SocialiteController

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function login()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function callback()
    {
        $user = Socialite::driver('discord')->user();
        dd($user);
    }
}
```

## Scopes

https://discordapp.com/developers/docs/topics/oauth2#shared-resources-oauth2-scopes

```php
    public function login()
    {
        return Socialite::driver('discord')
                        ->setScopes(['identify', 'email', 'guilds', 'guilds.join'])
                        ->redirect();
    }
```

## Demo
https://github.com/kawax/socialite-project

## LICENCE
MIT
Copyright kawax
