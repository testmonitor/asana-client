<?php

namespace TestMonitor\Asana\Resources;

class Attachment extends Resource
{
    /**
     * The id of the attachment.
     *
     * @var string
     */
    public $gid;

    /**
     * The filename of the attachment.
     *
     * @var string
     */
    public $name;

    /**
     * Create a new resource instance.
     *
     * @param string $name
     * @param string|null $gid
     */
    public function __construct(string $name, ?string $gid)
    {
        $this->name = $name;
        $this->gid = $gid;
    }
}
