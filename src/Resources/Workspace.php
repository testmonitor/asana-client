<?php

namespace TestMonitor\Asana\Resources;

use TestMonitor\Asana\Validator;

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
     * @param  array  $attributes
     */
    public function __construct(array $attributes)
    {
        Validator::keysExists($attributes, ['gid', 'name']);

        $this->gid = $attributes['gid'];
        $this->name = $attributes['name'];
    }
}
