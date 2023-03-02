<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use TestMonitor\Asana\Resources\Attachment;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

class AttachmentsTest extends TestCase
{
    protected $token;

    protected $attachment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\Asana\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->attachment = ['gid' => 1, 'name' => 'logo.png'];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_add_an_attachment_to_a_task()
    {
        // Given
        $asana = new \TestMonitor\Asana\Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $path = __DIR__ . '/files/logo.png';

        $service->shouldReceive('request')->once()->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode(['data' => $this->attachment]));

        // When
        $attachment = $asana->addAttachment(__DIR__ . '/files/logo.png', '12345');

        // Then
        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals($this->attachment['gid'], $attachment->gid);
        $this->assertEquals($this->attachment['name'], $attachment->name);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_add_an_attachment()
    {
        // Given
        $asana = new \TestMonitor\Asana\Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $path = __DIR__ . '/files/logo.png';

        $service->shouldReceive('request')->once()->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        )->andThrow(new UnauthorizedException());

        $response->shouldReceive('getStatusCode')->andReturn(403);

        $this->expectException(UnauthorizedException::class);

        // When
        $attachment = $asana->addAttachment(__DIR__ . '/files/logo.png', '12345');
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_find_task_to_add_attachment_to()
    {
        // Given
        $asana = new \TestMonitor\Asana\Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $path = __DIR__ . '/files/logo.png';

        $service->shouldReceive('request')->once()->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        )->andThrow(new NotFoundException());

        $response->shouldReceive('getStatusCode')->andReturn(404);

        $this->expectException(NotFoundException::class);

        // When
        $attachment = $asana->addAttachment(__DIR__ . '/files/logo.png', '12345');
    }
}
