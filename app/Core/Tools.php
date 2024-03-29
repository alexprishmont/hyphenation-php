<?php

namespace NXT\Core;

class Tools
{
    const CACHE_DEFAULT_EXPIRATION = 86400;
    const CACHE_DIR_MODE = 0775;
    const CACHE_FILE_MODE = 0664;

    public static function interpolate($message, array $context = []): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (self::isB($val))
                $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }

    public static function getDefaultCachePath(array $settings): string
    {
        $cachePath = dirname(__FILE__, 3) . $settings['OUTPUT_SRC'] . $settings['CACHE_OUTPUT_SRC'];
        assert(file_exists($cachePath));
        assert(is_writable($cachePath));
        return $cachePath;
    }

    private static function isB($val): bool
    {
        return !is_array($val) && (!is_object($val) || method_exists($val, '__toString'));
    }
}