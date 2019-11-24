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
     * @param string $gid
     * @param string $name
     */
    public function __construct(string $gid, string $name)
    {
        $this->gid = $gid;
        $this->name = $name;
    }
}
