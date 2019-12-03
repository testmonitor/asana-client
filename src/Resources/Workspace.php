<?php

namespace TestMonitor\Asana\Resources;

class Workspace extends Resource
{
    /**
     * The gid of the workspace.
     *
     * @var string
     */
    public $gid;

    /**
     * The name of the workspace.
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
