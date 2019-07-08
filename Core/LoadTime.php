<?php
namespace Core;

class LoadTime {
    private $timeStart = 0;

    public function __construct()
    {
        $this->timeStart = microtime(true);
    }

    public function __destruct()
    {
        $endTime = microtime(true) - $this->timeStart;
        print("\nScript execution time: $endTime seconds\n");
    }
}