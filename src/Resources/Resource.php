<?php

namespace TestMonitor\Asana\Resources;

class Resource
{
    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
