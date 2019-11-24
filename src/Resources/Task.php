<?php

namespace TestMonitor\Asana\Resources;

class Task extends Resource
{
    /**
     * The id of the task.
     *
     * @var string
     */
    public $gid;

    /**
     * The state of the task.
     *
     * @var boolean
     */
    public $completed;

    /**
     * The name of the task.
     *
     * @var string
     */
    public $name;

    /**
     * The notes for the task.
     *
     * @var string
     */
    public $notes;

    /**
     * The notes for the task.
     *
     * @var string
     */
    public $projectGid;

    /**
     * Create a new resource instance.
     *
     * @param bool $completed
     * @param string $name
     * @param string $notes
     * @param string|null $gid
     * @param string|null $projectGid
     */
    public function __construct(
        bool $completed,
        string $name,
        string $notes,
        ?string $projectGid = null,
        ?string $gid = null
    ) {
        $this->gid = $gid;
        $this->completed = $completed;
        $this->name = $name;
        $this->notes = $notes;
        $this->projectGid = $projectGid;
    }
}
