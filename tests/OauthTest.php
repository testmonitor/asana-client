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

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

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

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $this->expectException(TokenExpiredException::class);

        // When
        $asana = $asana->workspaces();
    }

    /** @test */
    public function it_should_provide_an_authorization_url()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], new AccessToken(), [], $dispatcher = Mockery::mock('\Asana\Dispatcher\OAuthDispatcher'));

        $state = 'somestate';

        $dispatcher->shouldReceive('authorizationUrl')->with($state)->andReturn('https://asana.authorization.url');

        // When
        $url = $asana->authorizationUrl($state);

        // Then
        $this->assertEquals('https://asana.authorization.url', $url);
    }

    /** @test */
    public function it_should_fetch_a_token()
    {
        // Given
        $dispatcher = Mockery::mock('\Asana\Dispatcher\OAuthDispatcher');

        $newToken = new AccessToken('12345', '567890', time() + 3600);

        $dispatcher->accessToken = $newToken->accessToken;
        $dispatcher->refreshToken = $newToken->refreshToken;
        $dispatcher->expiresIn = 3600;

        $code = 'somecode';

        $dispatcher->shouldReceive('fetchToken')->once()->andReturn($newToken->accessToken);

        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], new AccessToken(), [], $dispatcher);

        // When
        $token = $asana->fetchToken($code);

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals($token->accessToken, $newToken->accessToken);
        $this->assertEquals($token->refreshToken, $newToken->refreshToken);
    }

    /** @test */
    public function it_should_refresh_a_token()
    {
        // Given
        $oldToken = new AccessToken('12345', '567890', time() - 3600);

        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $oldToken, [], $dispatcher = Mockery::mock('\Asana\Dispatcher\OAuthDispatcher'));

        $newToken = new AccessToken('23456', '678901', time() + 3600);

        $dispatcher->accessToken = $newToken->accessToken;
        $dispatcher->refreshToken = $newToken->refreshToken;
        $dispatcher->expiresIn = 3600;

        $dispatcher->shouldReceive('refreshAccessToken')->once()->andReturn($newToken->accessToken);

        // When
        $token = $asana->refreshToken();

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals($token->accessToken, $newToken->accessToken);
        $this->assertEquals($token->refreshToken, $newToken->refreshToken);
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

    /** @test */
    public function it_should_throw_an_exception_when_refreshing_a_token_fails()
    {
        // Given
        $oldToken = new AccessToken('12345', '567890', time() - 3600);

        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $oldToken, [], $dispatcher = Mockery::mock('\Asana\Dispatcher\OAuthDispatcher'));

        $newToken = new AccessToken('23456', '678901', time() + 3600);

        $dispatcher->accessToken = $newToken->accessToken;
        $dispatcher->refreshToken = $newToken->refreshToken;
        $dispatcher->expiresIn = 3600;

        $dispatcher->shouldReceive('refreshAccessToken')->once()->andThrow(new \Exception());

        $this->expectException(UnauthorizedException::class);

        // When
        $token = $asana->refreshToken();
    }
}
