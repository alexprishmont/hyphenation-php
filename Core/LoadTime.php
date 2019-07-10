<?php

namespace Core;

class LoadTime
{
    private static $startTime;
    private static $endTime;

    public static function startMeasuring(): void
    {
        self::$startTime = microtime(true);
    }

    public static function endMeasuring(): void
    {
        self::$endTime = microtime(true);
    }

    public static function getTime(): string
    {
        return (string)(self::$endTime - self::$startTime);
    }
}