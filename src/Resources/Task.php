<?php

namespace TestMonitor\Asana\Resources;

use TestMonitor\Asana\Validator;

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
     * @var bool
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
     * @param  array  $attributes
     */
    public function __construct(array $attributes)
    {
        Validator::keysExists($attributes, ['name', 'notes']);

        $this->gid = $attributes['gid'] ?? null;
        $this->completed = $attributes['completed'] ?? false;
        $this->name = $attributes['name'];
        $this->notes = $attributes['notes'];
        $this->projectGid = $attributes['projectGid'] ?? null;
    }
}
