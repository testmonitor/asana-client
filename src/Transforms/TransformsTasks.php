<?php

namespace TestMonitor\Asana\Transforms;

use stdClass;
use TestMonitor\Asana\Resources\Task;

trait TransformsTasks
{
    /**
     * @param \TestMonitor\Asana\Resources\Task $task
     * @param string $projectGid
     * @return array
     */
    protected function toAsanaTask(Task $task, string $projectGid = null): array
    {
        return [
            'completed' => $task->completed,
            'projects' => [(string) $projectGid ?? $task->projectGid],
            'name' => $task->name,
            'html_notes' => '<body>' . $task->notes . '</body>',
        ];
    }

    /**
     * @param \stdClass $task
     * @return \TestMonitor\Asana\Resources\Task
     */
    protected function fromAsanaTask(stdClass $task): Task
    {
        return new Task([
            'completed' => $task->completed,
            'name' => $task->name ?? '',
            'notes' => $task->notes ?? '',
            'projectGid' => $task->projects[0]->gid ?? '',
            'gid' => $task->gid ?? '',
        ]);
    }
}
