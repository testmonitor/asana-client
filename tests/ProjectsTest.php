<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use TestMonitor\Asana\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Asana\Resources\Project;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

class ProjectsTest extends TestCase
{
    protected $token;

    protected $workspace;

    protected $project;

    protected $projects;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\Asana\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->workspace = ['gid' => '10', 'Workspace'];

        $this->project = ['gid' => '1', 'name' => 'Project'];

        $this->projects = [$this->project];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_list_of_projects()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode(['data' => $this->projects]));

        // When
        $projects = $asana->projects($this->workspace['gid']);

        // Then
        $this->assertIsArray($projects);
        $this->assertCount(1, $projects);
        $this->assertInstanceOf(Project::class, $projects[0]);
        $this->assertEquals($this->project['gid'], $projects[0]->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_list_of_projects()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', 'workspaces/10/projects', [])
            ->andThrow(new UnauthorizedException());

        $this->expectException(UnauthorizedException::class);

        // When
        $projects = $asana->projects($this->workspace['gid']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_get_a_list_of_projects_for_an_unknown_workspace()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', 'workspaces/unknown/projects', [])->andThrow(new NotFoundException());

        $this->expectException(NotFoundException::class);

        // When
        $projects = $asana->projects('unknown');
    }

    /** @test */
    public function it_should_return_a_single_project()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode(['data' => $this->project]));

        // When
        $project = $asana->project($this->project['gid']);

        // Then
        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals($this->project['gid'], $project->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_project()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', "projects/{$this->project['gid']}", [])
            ->andThrow(new UnauthorizedException());

        $this->expectException(UnauthorizedException::class);

        // When
        $project = $asana->project($this->project['gid']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_find_project()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', 'projects/unknown', [])->andThrow(new NotFoundException());

        $this->expectException(NotFoundException::class);

        // When
        $project = $asana->project('unknown');
    }
}
