<?php

namespace Core\Log;

use Core\Config;
use Core\Exceptions\LoggerException;
use Core\Log\Interfaces\LoggerInterface;
use Core\Tools;
use DateTime;
use DateTimeZone;

class Logger implements LoggerInterface
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config->get("logger");
    }

    public function getLoggerStatus(): bool
    {
        return $this->config['LOG_ENABLED'];
    }

    public function getValidPatternsLogStatus(): bool
    {
        return $this->config['LOG_VALID_PATTERNS'];
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
        $this->isLogLevelValid($level);
        $this->$level($message, $context);
    }

    private function processLog($message, array $context, $priority, string $priorityMessage, int $color)
    {
        if (!$this->config['LOG_ENABLED'])
            return;

        $date = new DateTime('now', new DateTimeZone($this->config['DATE_TIMEZONE']));
        $date = $date->format($this->config['DATE_FORMAT']);

        $log = Tools::interpolate($message, $context);

        if ($this->config['LOG_TO_CONSOLE']) {
            $finalLog = sprintf(
                "\033[%sm%s %s %s \033[0m" . PHP_EOL,
                $color,
                $date,
                $priorityMessage,
                print_r($log, true)
            );
            print($finalLog);
        }
        return $this->writeLog($log, $priority, $priorityMessage);
    }

    private function writeLog(string $log, string $priority, string $priorityMsg)
    {
        $date = new DateTime('now', new DateTimeZone($this->config['DATE_TIMEZONE']));
        $logName = $date->format("Y-m-d");

        $dir = dirname(__FILE__, 3) . $this->config['LOG_DIR'];

        $date = $date->format($this->config['DATE_FORMAT']);
        $data = $date . ' ' . $priorityMsg . ': ' . print_r($log, true) . PHP_EOL;

        $logFile = sprintf("%s/%s.%s",
            $dir, $logName,
            $this->config['LOG_EXT']
        );

        if (!file_exists($dir))
            mkdir($dir, 0700);

        if (!file_exists($logFile)) {
            $log = fopen($logFile, "w");
            fwrite($log, $data);
            fclose($log);
        } else {
            file_put_contents($logFile,
                $data,
                FILE_APPEND | LOCK_EX
            );
        }
    }

    private function isLogLevelValid(string $level)
    {
        if ($level != LogLevel::ERROR && $level != LogLevel::SUCCESS && $level != LogLevel::CRITICAL &&
            $level != LogLevel::DEBUG && $level != LogLevel::WARNING) {
            throw new LoggerException("Logger level {$level} does not exist.");
        }
    }
}