<?php

namespace TestMonitor\Asana\Actions;

use Asana\Errors\NotFoundError;
use Asana\Errors\ForbiddenError;
use Asana\Errors\InvalidTokenError;
use TestMonitor\Asana\Resources\Task;
use Asana\Errors\NoAuthorizationError;
use TestMonitor\Asana\Transforms\TransformsTasks;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

trait ManagesTasks
{
    use TransformsTasks;

    /**
     * Get a list of of tasks for a project.
     *
     * @param  string  $projectGid
     * @param  string  $fields
     * @return \TestMonitor\Asana\Resources\Task[]
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     */
    public function tasks($projectGid, $fields = 'name,notes,html_notes,completed,projects.gid')
    {
        try {
            $tasks = $this->client()->tasks->findByProject($projectGid, ['opt_fields' => $fields]);

            return array_map(function ($task) {
                return $this->fromAsanaTask($task);
            }, iterator_to_array($tasks));
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        } catch (NotFoundError $exception) {
            throw new NotFoundException($exception->getMessage());
        }
    }

    /**
     * Get a single task.
     *
     * @param  string  $gid
     * @param  string  $fields
     * @return \TestMonitor\Asana\Resources\Task
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     */
    public function task($gid, $fields = 'name,notes,html_notes,completed,projects.gid'): Task
    {
        try {
            $task = $this->client()->tasks->findById($gid, ['opt_fields' => $fields]);

            return $this->fromAsanaTask($task);
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        } catch (NotFoundError $exception) {
            throw new NotFoundException($exception->getMessage());
        }
    }

    /**
     * Create a new task.
     *
     * @param  \TestMonitor\Asana\Resources\Task  $task
     * @param  string  $projectGid
     * @param  string  $fields
     * @return Task
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     */
    public function createTask(
        Task $task,
        string $projectGid,
        $fields = 'name,notes,html_notes,completed,projects.gid'
    ): Task {
        try {
            $task = $this->client()->tasks->create($this->toAsanaTask($task, $projectGid), ['opt_fields' => $fields]);

            return $this->fromAsanaTask($task);
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        } catch (NotFoundError $exception) {
            throw new NotFoundException($exception->getMessage());
        }
    }
}
