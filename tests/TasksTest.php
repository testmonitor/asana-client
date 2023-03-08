<?php

namespace TestMonitor\Asana\Tests;

use Mockery;
use TestMonitor\Asana\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Asana\Resources\Task;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

class TasksTest extends TestCase
{
    protected $token;

    protected $project;

    protected $task;

    protected $tasks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\Asana\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->project = ['gid' => '10', 'Project'];

        $this->task = ['gid' => '1', 'name' => 'Task', 'notes' => 'Notes', 'completed' => false];

        $this->tasks = [$this->task];
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

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', "projects/{$this->project['gid']}/tasks", [])->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode(['data' => $this->tasks]));

        // When
        $tasks = $asana->tasks($this->project['gid']);

        // Then
        $this->assertIsArray($tasks);
        $this->assertCount(1, $tasks);
        $this->assertInstanceOf(Task::class, $tasks[0]);
        $this->assertEquals($this->task['gid'], $tasks[0]->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_list_of_tasks()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', "projects/{$this->project['gid']}/tasks", [])
            ->andThrow(new UnauthorizedException());

        $this->expectException(UnauthorizedException::class);

        // When
        $tasks = $asana->tasks($this->project['gid']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_get_a_list_of_tasks_for_an_unknown_workspace()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', 'projects/unknown/tasks', [])
            ->andThrow(new NotFoundException());

        $this->expectException(NotFoundException::class);

        // When
        $tasks = $asana->tasks('unknown');
    }

    /** @test */
    public function it_should_return_a_single_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', "tasks/{$this->task['gid']}", [])->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode(['data' => $this->task]));

        // When
        $task = $asana->task($this->task['gid']);

        // Then
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($this->task['gid'], $task->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_get_a_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', "tasks/{$this->task['gid']}", [])
            ->andThrow(new UnauthorizedException());

        $this->expectException(UnauthorizedException::class);

        // When
        $task = $asana->task($this->task['gid']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_find_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->with('GET', 'tasks/unknown', [])
            ->andThrow(new NotFoundException());

        $this->expectException(NotFoundException::class);

        // When
        $asana->task('unknown');
    }

    /** @test */
    public function it_should_create_a_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn(
            $response = Mockery::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->andReturn(201);
        $response->shouldReceive('getBody')->andReturn(json_encode(['data' => $this->task]));

        // When
        $task = $asana->createTask(new Task([
            'completed' => $this->task['completed'],
            'name' => $this->task['name'],
            'notes' => $this->task['notes'],
            'projectGid' => $this->project['gid'],
        ]), $this->project['gid']);

        // Then
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($this->task['gid'], $task->gid);
    }

    /** @test */
    public function it_should_throw_an_unauthorized_exception_when_client_lacks_authorization_to_create_a_task()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()
            ->andThrow(new UnauthorizedException());

        $this->expectException(UnauthorizedException::class);

        // When
        $asana->createTask(new Task([
            'completed' => $this->task['completed'],
            'name' => $this->task['name'],
            'notes' => $this->task['notes'],
            'projectGid' => $this->project['gid'],
        ]), $this->project['gid']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_cannot_find_project_to_create_task_in()
    {
        // Given
        $asana = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], $this->token);

        $asana->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()
            ->andThrow(new NotFoundException());

        $this->expectException(NotFoundException::class);

        // When
        $asana->createTask(new Task([
            'completed' => $this->task['completed'],
            'name' => $this->task['name'],
            'notes' => $this->task['notes'],
        ]), 'unknown');
    }
}
