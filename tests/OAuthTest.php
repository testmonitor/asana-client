<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use TestMonitor\Asana\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Asana\AccessToken;
use TestMonitor\Asana\Exceptions\TokenExpiredException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

class OauthTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_create_a_token()
    {
        // When
        $token = new AccessToken('12345', '67890', time() + 3600);

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertIsArray($token->toArray());
        $this->assertFalse($token->expired());
    }

    /** @test */
    public function it_should_detect_an_expired_token()
    {
        // Given
        $token = new AccessToken('12345', '67890', time() - 60);

        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        // When
        $expired = $asana->tokenExpired();

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertTrue($token->expired());
        $this->assertTrue($expired);
    }

    /** @test */
    public function it_should_not_provide_a_client_with_an_expired_token()
    {
        // Given
        $token = new AccessToken('12345', '67890', time() - 60);

        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $this->expectException(TokenExpiredException::class);

        // When
        $asana = $asana->workspaces();
    }

    /** @test */
    public function it_should_provide_an_authorization_url()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], new AccessToken(), $provider = Mockery::mock('\TestMonitor\Asana\Provider\AsanaProvider'));

        $options = ['state' => 'somestate', 'scope' => 'incoming-webhook'];

        $provider->shouldReceive('getAuthorizationUrl')->with($options)->andReturn('https://asana.authorization.url');

        // When
        $url = $asana->authorizationUrl($options);

        // Then
        $this->assertEquals('https://asana.authorization.url', $url);
    }

    /** @test */
    public function it_should_fetch_a_token()
    {
        // Given
        $provider = Mockery::mock('\TestMonitor\Asana\Provider\AsanaProvider');

        $token = Mockery::mock('\League\OAuth2\Client\Token\AccessToken');

        $token->shouldReceive('getToken')->once()->andReturn('12345');
        $token->shouldReceive('getRefreshToken')->once()->andReturn('123456');
        $token->shouldReceive('getExpires')->once()->andReturn(time() + 3600);

        $provider->shouldReceive('getAccessToken')->with('authorization_code', ['code' => '123'])->once()->andReturn($token);

        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], new AccessToken(), $provider);

        // When
        $token = $asana->fetchToken('123');

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals('12345', $token->accessToken);
        $this->assertEquals('123456', $token->refreshToken);
    }

    /** @test */
    public function it_should_refresh_a_token()
    {
        // Given
        $oldToken = new AccessToken('12345', '567890', time() - 3600);

        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUri' => 'none'], $oldToken, $provider = Mockery::mock('\TestMonitor\Asana\Provider\AsanaProvider'));

        $token = Mockery::mock('\League\OAuth2\Client\Token\AccessToken');

        $token->shouldReceive('getToken')->once()->andReturn('12345');
        $token->shouldReceive('getRefreshToken')->once()->andReturn('123456');
        $token->shouldReceive('getExpires')->once()->andReturn(time() + 3600);

        $provider->shouldReceive('getAccessToken')->with('refresh_token', ['refresh_token' => '567890'])->once()->andReturn($token);

        // When
        $token = $asana->refreshToken();

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals('12345', $token->accessToken);
        $this->assertEquals('123456', $token->refreshToken);
    }

    /** @test */
    public function it_should_not_refresh_a_token_without_a_refresh_token()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none']);

        $this->expectException(UnauthorizedException::class);

        // When
        $asana->refreshToken();
    }

    /** @test */
    public function it_should_not_provide_a_client_without_a_token()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none']);

        $this->expectException(UnauthorizedException::class);

        // When
        $asana->workspaces();
    }
}
