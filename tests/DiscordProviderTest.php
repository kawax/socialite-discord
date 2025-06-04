<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use Mockery as m;
use Revolution\Socialite\Discord\DiscordProvider;

class DiscordProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_redirect_generates_correct_url()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $url = $response->getTargetUrl();
        $this->assertStringStartsWith('https://discordapp.com/api/oauth2/authorize', $url);
        $this->assertStringContainsString('client_id=client_id', $url);
        $this->assertStringContainsString('redirect_uri=redirect', $url);
        $this->assertStringContainsString('scope=identify+email', $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    public function test_redirect_with_custom_scopes()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->scopes(['identify', 'email', 'guilds', 'guilds.join']);
        $response = $provider->redirect();

        $url = $response->getTargetUrl();
        $this->assertStringContainsString('scope=identify+email+guilds+guilds.join', $url);
    }

    public function test_get_auth_url_returns_correct_url()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');

        $url = $provider->redirect()->getTargetUrl();
        $this->assertStringStartsWith('https://discordapp.com/api/oauth2/authorize', $url);
    }

    public function test_get_token_url_returns_correct_url()
    {
        $request = Request::create('foo');
        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getTokenUrl');
        $method->setAccessible(true);

        $tokenUrl = $method->invoke($provider);
        $this->assertEquals('https://discordapp.com/api/oauth2/token', $tokenUrl);
    }

    public function test_user_retrieval_with_complete_data()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('A', 40));

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'expires_in' => 3600,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => '123456789012345678',
            'username' => 'testuser',
            'discriminator' => '1234',
            'global_name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'a1b2c3d4e5f6g7h8i9j0',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('123456789012345678', $user->getId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('testuser#1234', $user->getNickname());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('https://cdn.discordapp.com/avatars/123456789012345678/a1b2c3d4e5f6g7h8i9j0.jpg', $user->getAvatar());
        $this->assertEquals('access_token_123', $user->token);
        $this->assertEquals('refresh_token_456', $user->refreshToken);
        $this->assertEquals(3600, $user->expiresIn);
    }

    public function test_user_retrieval_with_missing_optional_fields()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('B', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('B', 40));

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_456',
            'expires_in' => 7200,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => '987654321098765432',
            'username' => 'testuser2',
            'discriminator' => '5678',
            'avatar' => null,
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('987654321098765432', $user->getId());
        $this->assertEquals('testuser2', $user->getName());
        $this->assertEquals('testuser2#5678', $user->getNickname());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getAvatar());
        $this->assertEquals('access_token_456', $user->token);
        $this->assertNull($user->refreshToken);
        $this->assertEquals(7200, $user->expiresIn);
    }

    public function test_user_retrieval_with_null_avatar()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('C', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('C', 40));

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_789',
            'refresh_token' => 'refresh_token_789',
            'expires_in' => 1800,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => '111222333444555666',
            'username' => 'testuser3',
            'discriminator' => '9999',
            'global_name' => 'Test User 3',
            'email' => 'test3@example.com',
            'avatar' => null,
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('111222333444555666', $user->getId());
        $this->assertEquals('Test User 3', $user->getName());
        $this->assertEquals('testuser3#9999', $user->getNickname());
        $this->assertEquals('test3@example.com', $user->getEmail());
        $this->assertNull($user->getAvatar());
    }

    public function test_scopes_configuration()
    {
        $request = Request::create('foo');
        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');

        $this->assertEquals(['identify', 'email'], $provider->getScopes());
    }

    public function test_provider_with_custom_scopes()
    {
        $request = Request::create('foo');
        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');

        $provider->scopes(['identify', 'email', 'guilds', 'guilds.join']);

        $this->assertEquals(['identify', 'email', 'guilds', 'guilds.join'], $provider->getScopes());
    }

    public function test_user_profile_request_uses_bearer_token()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('D', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('D', 40));

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'bearer_token_test',
            'expires_in' => 3600,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => 'test_user_id_123',
            'username' => 'bearertest',
            'discriminator' => '0001',
            'global_name' => 'Bearer Test User',
            'email' => 'bearer@discord.com',
            'avatar' => 'test_avatar_hash',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test_user_id_123', $user->getId());
        $this->assertEquals('Bearer Test User', $user->getName());
        $this->assertEquals('bearer@discord.com', $user->getEmail());
        $this->assertEquals('bearer_token_test', $user->token);
    }

    public function test_scope_separator_is_space()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->scopes(['identify', 'email', 'guilds', 'guilds.join']);
        $response = $provider->redirect();

        $url = $response->getTargetUrl();
        $this->assertStringContainsString('scope=identify+email+guilds+guilds.join', $url);
    }

    public function test_get_user_by_token_method()
    {
        $request = Request::create('foo');
        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');

        $userResponse = new Response(200, [], json_encode([
            'id' => 'direct_token_test',
            'username' => 'directtest',
            'discriminator' => '0002',
            'global_name' => 'Direct Token Test',
            'email' => 'direct@discord.com',
            'avatar' => 'direct_avatar_hash',
        ]));

        $mock = new MockHandler([$userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getUserByToken');
        $method->setAccessible(true);

        $userData = $method->invoke($provider, 'test_token');

        $this->assertEquals('direct_token_test', $userData['id']);
        $this->assertEquals('directtest', $userData['username']);
        $this->assertEquals('Direct Token Test', $userData['global_name']);
        $this->assertEquals('direct@discord.com', $userData['email']);
    }

    public function test_map_user_to_object_method()
    {
        $request = Request::create('foo');
        $provider = new DiscordProvider($request, 'client_id', 'client_secret', 'redirect');

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('mapUserToObject');
        $method->setAccessible(true);

        $userData = [
            'id' => 'map_test_id',
            'username' => 'maptest',
            'discriminator' => '0003',
            'global_name' => 'Map Test User',
            'email' => 'map@discord.com',
            'avatar' => 'map_avatar_hash',
        ];

        $user = $method->invoke($provider, $userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('map_test_id', $user->getId());
        $this->assertEquals('Map Test User', $user->getName());
        $this->assertEquals('maptest#0003', $user->getNickname());
        $this->assertEquals('map@discord.com', $user->getEmail());
        $this->assertEquals('https://cdn.discordapp.com/avatars/map_test_id/map_avatar_hash.jpg', $user->getAvatar());
    }
}
