<?php

namespace NXT\Core;

class Timing
{
    private $startTime;
    private $endTime;

    public function start(): void
    {
        $this->startTime = microtime(true);
    }

    public function stop(): void
    {
        $this->endTime = microtime(true);
    }

    public function printTiming(): string
    {
        return $this->getExecutionTime();
    }

    private function getExecutionTime(): string
    {
        return $this->endTime - $this->startTime;
    }
}