<?php

namespace Core\Log;

use Core\Log\Interfaces\LoggerInterface;
use mysql_xdevapi\Exception;

class Logger implements LoggerInterface
{
    public function critical($message, array $context = []): void
    {

    }

    public function error($message, array $context = []): void
    {

    }

    public function warning($message, array $context = []): void
    {

    }

    public function success($message, array $context = []): void
    {

    }

    public function debug($message, array $context = []): void
    {

    }

    public function log($level, $message, array $context = []): void
    {
        if (!$this->isLogLevelValid($level))
            throw new Exception("[ Logger ] Not valid log level! [$level]");

        return $level($message, $context);
    }

    private function isLogLevelValid($level): bool
    {
        if ($level !== LogLevel::ERROR || $level !== LogLevel::SUCCESS || $level !== LogLevel::CRITICAL ||
            $level !== LogLevel::DEBUG || $level !== LogLevel::WARNING) {
            return false;
        }
        return true;
    }
}