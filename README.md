# Socialite for Discord

https://discordapp.com/developers/docs/topics/oauth2

## Installation
```
composer require revolution/socialite-discord
```

### config/services.php

```
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
```
Route::get('login', 'SocialiteController@login');
Route::get('callback', 'SocialiteController@callback');
```

SocialiteController

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;

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
