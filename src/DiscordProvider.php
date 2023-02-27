<?php

namespace Revolution\Socialite\Discord;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class DiscordProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'identify',
        'email',
    ];

    /**
     * @var string
     */
    protected string $endpoint = 'https://discordapp.com/api/';

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        $url = $this->endpoint.'oauth2/authorize';

        return $this->buildAuthUrlFromBase($url, $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->endpoint.'oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()
                         ->get($this->endpoint.'users/@me', [
                             'headers' => [
                                 'Authorization' => 'Bearer '.$token,
                             ],
                         ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => sprintf('%s#%s', $user['username'], $user['discriminator']),
            'name' => $user['username'],
            'email' => $user['email'] ?? null,
            'avatar' => is_null($user['avatar']) ? null :
                sprintf('https://cdn.discordapp.com/avatars/%s/%s.jpg', $user['id'], $user['avatar']),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code): array
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
