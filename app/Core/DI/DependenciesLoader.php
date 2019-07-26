<?php
declare(strict_types=1);


namespace Core\DI;

use Core\Config;

class DependenciesLoader
{
    public static function get(): array
    {
        $config = new Config;
        return $config->get('dependencies');
    }
}