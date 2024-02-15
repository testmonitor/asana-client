<?php

namespace TestMonitor\Asana\Exceptions;

class UnauthorizedException extends \Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
