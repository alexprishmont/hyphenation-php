<?php

namespace NXT\Core\Exceptions;

use NXT\Core\Log\Logger;
use NXT\Core\Log\LogLevel;

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
            $this->logger->log(LogLevel::ERROR, $exception->getMessage());
        else
            print("Exception caught: ".$exception->getMessage());
    }
}