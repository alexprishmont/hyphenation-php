<?php

namespace NXT\Core\Exceptions;

use \Exception;
use Throwable;

class InvalidArgumentException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return "[ERROR] [{$this->code}]: {$this->message}\n";
    }
}