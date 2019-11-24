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
     * @param string $gid
     * @param string $name
     */
    public function __construct(string $gid, string $name)
    {
        $this->gid = $gid;
        $this->name = $name;
    }
}
