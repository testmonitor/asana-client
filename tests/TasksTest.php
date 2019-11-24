<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use TestMonitor\Asana\Client;
use Asana\Errors\NotFoundError;
use PHPUnit\Framework\TestCase;
use TestMonitor\Asana\Resources\Task;
use Asana\Errors\NoAuthorizationError;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

class TasksTest extends TestCase
{
    protected $token;

    protected $project;

    protected $task;

    protected $tasks;

    protected $optFields = 'name,notes,html_notes,completed,projects.gid';

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\Asana\Token');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->project = (object) ['gid' => '10', 'Project'];

        $this->task = (object) ['gid' => '1', 'name' => 'Task', 'notes' => 'Notes'];

        $this->tasks = Mockery::mock('\Asana\Iterator\ItemIterator');
        $this->tasks->shouldReceive('rewind')->andReturnNull();
        $this->tasks->shouldReceive('next')->andReturnNull();
        $this->tasks->shouldReceive('valid')->atMost()->times(1)->andReturnTrue();
        $this->tasks->shouldReceive('valid')->andReturnFalse();
        $this->tasks->shouldReceive('key')->andReturn(0);
        $this->tasks->shouldReceive('current')->andReturn($this->task);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_list_of_tasks()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->tasks = Mockery::mock('\Asana\Resources\Tasks');
        $service->tasks->shouldReceive('findByProject')->once()->with($this->project->gid, ['opt_fields' => $this->optFields])->andReturn(
            $this->tasks
        );

        // When
        $tasks = $asana->tasks($this->project->gid);

        // Then
        $this->assertIsArray($tasks);
        $this->assertCount(1, $tasks);
        $this->assertInstanceOf(Task::class, $tasks[0]);
        $this->assertEquals($this->task->gid, $tasks[0]->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_list_of_tasks()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->tasks = Mockery::mock('\Asana\Resources\Tasks');
        $service->tasks->shouldReceive('findByProject')->once()->with($this->project->gid, ['opt_fields' => $this->optFields])
            ->andThrow(new NoAuthorizationError([]));

        $this->expectException(UnauthorizedException::class);

        // When
        $tasks = $asana->tasks($this->project->gid);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_get_a_list_of_tasks_for_an_unknown_workspace()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->tasks = Mockery::mock('\Asana\Resources\Tasks');
        $service->tasks->shouldReceive('findByProject')->once()->with('unknown', ['opt_fields' => $this->optFields])
            ->andThrow(new NotFoundError([]));

        $this->expectException(NotFoundException::class);

        // When
        $tasks = $asana->tasks('unknown');
    }

    /** @test */
    public function it_should_return_a_single_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->tasks = Mockery::mock('\Asana\Resources\Tasks');
        $service->tasks->shouldReceive('findById')->once()->with($this->task->gid, ['opt_fields' => $this->optFields])->andReturn(
            $this->task
        );

        // When
        $task = $asana->task($this->task->gid);

        // Then
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($this->task->gid, $task->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->tasks = Mockery::mock('\Asana\Resources\Tasks');
        $service->tasks->shouldReceive('findById')->once()->with($this->task->gid, ['opt_fields' => $this->optFields])
            ->andThrow(new NoAuthorizationError([]));

        $this->expectException(UnauthorizedException::class);

        // When
        $task = $asana->task($this->task->gid);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_find_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\Asana\Client'));

        $service->tasks = Mockery::mock('\Asana\Resources\Tasks');
        $service->tasks->shouldReceive('findById')->once()->with('unknown', ['opt_fields' => $this->optFields])
            ->andThrow(new NotFoundError([]));

        $this->expectException(NotFoundException::class);

        // When
        $task = $asana->task('unknown');
    }
}
