<?php

namespace TestMonitor\Jira\Tests;

use Mockery;
use Asana\Errors\NotFoundError;
use PHPUnit\Framework\TestCase;
use Asana\Errors\NoAuthorizationError;
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

        $this->token = Mockery::mock('\TestMonitor\Asana\Token');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->attachment = (object) ['gid' => 1, 'name' => 'logo.png'];
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

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $path = __DIR__ . '/files/logo.png';

        $service->attachments = Mockery::mock('\Asana\Resources\Attachments');
        $service->attachments->shouldReceive('createOnTask')->once()->with('12345', file_get_contents($path), basename($path), mime_content_type($path))->andReturn(
            $this->attachment
        );

        // When
        $attachment = $asana->addAttachment(__DIR__ . '/files/logo.png', '12345');

        // Then
        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals($this->attachment->gid, $attachment->gid);
        $this->assertEquals($this->attachment->name, $attachment->name);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_add_an_attachment()
    {
        // Given
        $asana = new \TestMonitor\Asana\Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $path = __DIR__ . '/files/logo.png';

        $service->attachments = Mockery::mock('\Asana\Resources\Attachments');
        $service->attachments->shouldReceive('createOnTask')->once()->with('12345', file_get_contents($path), basename($path), mime_content_type($path))
            ->andThrow(new NoAuthorizationError([]));

        $this->expectException(UnauthorizedException::class);

        // When
        $attachment = $asana->addAttachment(__DIR__ . '/files/logo.png', '12345');
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_find_task_to_add_attachment_to()
    {
        // Given
        $asana = new \TestMonitor\Asana\Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $path = __DIR__ . '/files/logo.png';

        $service->attachments = Mockery::mock('\Asana\Resources\Attachments');
        $service->attachments->shouldReceive('createOnTask')->once()->with('12345', file_get_contents($path), basename($path), mime_content_type($path))
            ->andThrow(new NotFoundError([]));

        $this->expectException(NotFoundException::class);

        // When
        $attachment = $asana->addAttachment(__DIR__ . '/files/logo.png', '12345');
    }
}
