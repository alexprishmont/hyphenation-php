<?php

namespace Core\Log;

use Core\Log\Interfaces\LoggerInterface;
use Core\Tools;
use DateTime;
use DateTimeZone;

class Logger implements LoggerInterface
{
    private $config;

    public function __construct(object $config)
    {
        $this->config = $config->getConfigSettings();
    }

    public function critical($message, array $context = [])
    {
        return $this->processLog($message,
            $context,
            LogLevel::CRITICAL,
            "[critical]",
            LogLevel::CRITICAL_COLOR
        );
    }

    public function error($message, array $context = [])
    {
        return $this->processLog($message,
            $context,
            LogLevel::ERROR,
            "[error]",
            LogLevel::ERROR_COLOR
        );
    }

    public function warning($message, array $context = [])
    {
        return $this->processLog($message,
            $context,
            LogLevel::WARNING,
            "[warning]",
            LogLevel::WARNING_COLOR
        );
    }

    public function success($message, array $context = [])
    {
        return $this->processLog($message,
            $context,
            LogLevel::SUCCESS,
            "[success]",
            LogLevel::SUCCESS_COLOR
        );
    }

    public function debug($message, array $context = [])
    {
        return $this->processLog($message,
            $context,
            LogLevel::DEBUG,
            "[debug]",
            LogLevel::DEBUG_COLOR
        );
    }

    public function log($level, $message, array $context = [])
    {
       // if (!$this->isLogLevelValid($level))
        return $this->$level($message, $context);
    }

    private function processLog($message, array $context, $priority, string $priorityMessage, int $color)
    {
        $date = new DateTime('now', new DateTimeZone($this->config['DATE_TIMEZONE']));
        $date = $date->format($this->config['DATE_FORMAT']);

        $log = Tools::interpolate($message, $context);

        if (!$this->config['LOG_TO_CONSOLE'])
            return $this->writeLog($log, $priority, $priorityMessage);

        $finalLog = sprintf(
            "\033[%sm%s %s %s \033[0m" . PHP_EOL,
            $color,
            $date,
            $priorityMessage,
            print_r($log, true)
        );

        print($finalLog);
    }

    private function writeLog(string $log, string $priority, string $priorityMsg)
    {

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