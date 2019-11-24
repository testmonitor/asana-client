<?php

namespace TestMonitor\Asana\Transforms;

use stdClass;
use TestMonitor\Asana\Resources\Task;

trait TransformsTasks
{
    /**
     * @param \TestMonitor\Asana\Resources\Task $task
     * @return array
     */
    protected function toAsanaTask(Task $task): array
    {
        return [
            'completed' => $task->completed,
            'projects' => [(string) $task->projectGid],
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
        return new Task(
            $task->completed ?? false,
            $task->name ?? '',
            $task->notes ?? '',
            $task->projects[0]->gid ?? '',
            $task->gid ?? ''
        );
    }
}
