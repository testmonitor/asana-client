<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use TestMonitor\Asana\Client;
use PHPUnit\Framework\TestCase;
use Asana\Errors\NoAuthorizationError;
use TestMonitor\Asana\Resources\Project;
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

        $this->token = Mockery::mock('\TestMonitor\Asana\Token');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->workspace = (object) ['gid' => '10', 'Workspace'];

        $this->project = (object) ['gid' => '1', 'name' => 'Project'];

        $this->projects = Mockery::mock('\Asana\Iterator\ItemIterator');
        $this->projects->shouldReceive('rewind')->andReturnNull();
        $this->projects->shouldReceive('next')->andReturnNull();
        $this->projects->shouldReceive('valid')->atMost()->times(1)->andReturnTrue();
        $this->projects->shouldReceive('valid')->andReturnFalse();
        $this->projects->shouldReceive('key')->andReturn(0);
        $this->projects->shouldReceive('current')->andReturn($this->project);
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

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->projects = Mockery::mock('\Asana\Resources\Projects');
        $service->projects->shouldReceive('findByWorkspace')->once()->with($this->workspace->gid)->andReturn(
            $this->projects
        );

        // When
        $projects = $asana->projects($this->workspace->gid);

        // Then
        $this->assertIsArray($projects);
        $this->assertCount(1, $projects);
        $this->assertInstanceOf(Project::class, $projects[0]);
        $this->assertEquals($this->project->gid, $projects[0]->gid);
    }

    /** @test */
    public function it_should_throw_an_exception_when_client_fails_to_get_a_list_of_projects()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->projects = Mockery::mock('\Asana\Resources\Projects');
        $service->projects->shouldReceive('findByWorkspace')->once()->with($this->workspace->gid)->andThrow(new NoAuthorizationError([]));

        $this->expectException(UnauthorizedException::class);

        // When
        $projects = $asana->projects($this->workspace->gid);
    }

    /** @test */
    public function it_should_return_a_single_project()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->projects = Mockery::mock('\Asana\Resources\Projects');
        $service->projects->shouldReceive('findById')->once()->with($this->project->gid)->andReturn(
            $this->project
        );

        // When
        $project = $asana->project($this->project->gid);

        // Then
        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals($this->project->gid, $project->gid);
    }

    /** @test */
    public function it_should_throw_an_exception_when_client_fails_to_get_a_single_project()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->projects = Mockery::mock('\Asana\Resources\Projects');
        $service->projects->shouldReceive('findById')->once()->with($this->project->gid)->andThrow(new NoAuthorizationError([]));

        $this->expectException(UnauthorizedException::class);

        // When
        $project = $asana->project($this->project->gid);
    }
}
