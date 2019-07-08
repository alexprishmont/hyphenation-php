<?php

namespace Core;

use Core\Log\Logger;
use Core\Log\LogLevel;

class LoadTime
{
    private $timeStart = 0;
    private $logger;

    public function __construct(Logger $logger = null)
    {
        $this->timeStart = microtime(true);
        $this->logger = $logger;
    }

    public function __destruct()
    {
        $endTime = microtime(true) - $this->timeStart;
        if (isset($this->logger)) {
            print("\n");
            $this->logger->log(LogLevel::SUCCESS, "Script execution time {time}", ['time' => $endTime]);
            print("\n");
        } else
            print("\nScript execution time: $endTime seconds\n");
    }
}