<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use TestMonitor\Asana\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_create_a_token()
    {
        // When
        $token = new Token('12345', '67890', time() + 3600);

        // Then
        $this->assertInstanceOf(Token::class, $token);
        $this->assertIsArray($token->toArray());
        $this->assertFalse($token->expired());
    }

    /** @test */
    public function it_should_detect_an_expired_token()
    {
        // Given
        $token = new Token('12345', '67890', time() - 60);

        // When
        $expired = $token->expired();

        // Then
        $this->assertInstanceOf(Token::class, $token);
        $this->assertTrue($expired);
    }
}
