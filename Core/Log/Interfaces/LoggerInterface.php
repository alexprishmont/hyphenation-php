<?php

namespace Core\Log\Interfaces;

interface LoggerInterface
{
    public function critical($message, array $context = []): void;

    public function error($message, array $context = []): void;

    public function warning($message, array $context = []): void;

    public function success($message, array $context = []): void;

    public function debug($message, array $context = []): void;

    public function log($level, $message, array $context = []): void;
}