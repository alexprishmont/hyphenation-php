<?php
declare(strict_types=1);

namespace NXT\Core\Database;

use NXT\Core\Config;

class DatabaseSettings
{
    public static function get(): array
    {
        $config = new Config;
        return $config->get('database');
    }
}