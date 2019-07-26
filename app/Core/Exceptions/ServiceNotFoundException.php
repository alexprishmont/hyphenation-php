<?php

namespace NXT\Core\Exceptions;

use Exception;
use Throwable;

class ServiceNotFoundException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return "[SERVICE] [{$this->code}]: {$this->message}\n";
    }

}