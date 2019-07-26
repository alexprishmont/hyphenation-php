<?php

namespace NXT\Core;

class Timing
{
    private $startTime;
    private $endTime;

    public function start()
    {
        $this->startTime = microtime(true);
    }

    public function stop()
    {
        $this->endTime = microtime(true);
    }

    public function printTiming()
    {
        return $this->getExecutionTime();
    }

    private function getExecutionTime()
    {
        return $this->endTime - $this->startTime;
    }
}