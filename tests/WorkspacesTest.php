<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use TestMonitor\Asana\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Asana\Resources\Workspace;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

class WorkspacesTest extends TestCase
{
    protected $token;

    protected $workspace;

    protected $workspaces;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\Asana\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->workspace = ['gid' => '1', 'name' => 'Workspace'];

        $this->workspaces = [$this->workspace];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_list_of_workspaces()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode(['data' => $this->workspaces]));

        // When
        $workspaces = $asana->workspaces();

        // Then
        $this->assertIsArray($workspaces);
        $this->assertCount(1, $workspaces);
        $this->assertInstanceOf(Workspace::class, $workspaces[0]);
        $this->assertEquals($this->workspace['gid'], $workspaces[0]->gid);
        $this->assertIsArray($workspaces[0]->toArray());
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_list_of_workspaces()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andThrow(new UnauthorizedException());

        $this->expectException(UnauthorizedException::class);

        // When
        $workspaces = $asana->workspaces();
    }

    /** @test */
    public function it_should_return_a_single_workspace()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', "workspaces/{$this->workspace->gid}", [])->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode((array) $this->workspace));

        // When
        $workspace = $asana->workspace($this->workspace->gid);

        // Then
        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertEquals($this->workspace['gid'], $workspace->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_workspace()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', "workspaces/{$this->workspace->gid}", [])->andThrow(new UnauthorizedException());

        $this->expectException(UnauthorizedException::class);

        // When
        $workspace = $asana->workspace($this->workspace->gid);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_find_workspace()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', 'workspaces/unknown', [])->andThrow(new NotFoundException());

        $this->expectException(NotFoundException::class);

        // When
        $workspace = $asana->workspace('unknown');
    }
}
