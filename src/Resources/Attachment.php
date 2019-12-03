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
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->gid = $attributes['gid'] ?? null;
        $this->name = $attributes['name'];
    }
}
