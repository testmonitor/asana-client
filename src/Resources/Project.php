<?php

namespace TestMonitor\Asana\Resources;

class Project extends Resource
{
    /**
     * The id of the project.
     *
     * @var string
     */
    public $gid;

    /**
     * The name of the project.
     *
     * @var string
     */
    public $name;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->gid = $attributes['gid'];
        $this->name = $attributes['name'];
    }
}
