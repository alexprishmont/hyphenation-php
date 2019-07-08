<?php

namespace Core\Log\Interfaces;

interface LoggerInterface
{
    public function critical($message, array $context = []);

    public function error($message, array $context = []);

    public function warning($message, array $context = []);

    public function success($message, array $context = []);

    public function debug($message, array $context = []);

    public function log($level, $message, array $context = []);
}