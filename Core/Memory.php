<?php

namespace Core;

class Memory
{
    public static function get()
    {
        return self::convert(memory_get_usage(true));
    }

    private function convert($size)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}