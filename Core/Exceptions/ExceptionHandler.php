<?php

namespace Core\Exceptions;

use Core\Log\Logger;
use Core\Log\LogLevel;

class ExceptionHandler
{
    private $logger;

    public function __construct(Logger $log)
    {
        $this->logger = $log;
    }


    public function exceptionHandlerFunction($exception)
    {
        if (isset($this->logger))
            $this->logger->log(LogLevel::WARNING, $exception->getMessage());
        else
            print("Exception caught: ".$exception->getMessage());
    }
}