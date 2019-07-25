<?php
namespace Core\Exceptions;

use Throwable;

class InvalidFlagException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return "[INPUT] [{$this->code}]: {$this->message}";
    }
}