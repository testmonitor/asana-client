<?php

namespace TestMonitor\Asana\Actions;

use TestMonitor\Asana\Resources\Task;
use TestMonitor\Asana\Transforms\TransformsTasks;

trait ManagesTasks
{
    use TransformsTasks;

    /**
     * Get a list of of tasks for a project.
     *
     * @param string $projectGid
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     *
     * @return \TestMonitor\Asana\Resources\Task[]
     */
    public function tasks($projectGid)
    {
        $tasks = $this->get("projects/{$projectGid}/tasks");

        return array_map(function ($task) {
            return $this->fromAsanaTask($task);
        }, $tasks['data']);
    }

    /**
     * Get a single task.
     *
     * @param string $gid
     * @param string $fields
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     *
     * @return \TestMonitor\Asana\Resources\Task
     */
    public function task($gid): Task
    {
        $task = $this->get("tasks/{$gid}");

        return $this->fromAsanaTask($task['data']);
    }

    /**
     * Create a new task.
     *
     * @param \TestMonitor\Asana\Resources\Task $task
     * @param string $projectGid
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     *
     * @return Task
     */
    public function createTask(
        Task $task,
        string $projectGid
    ): Task {
        $task = $this->post('tasks', $this->toAsanaTask($task, $projectGid));

        return $this->fromAsanaTask($task['data']);
    }
}
