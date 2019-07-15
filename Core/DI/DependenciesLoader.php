<?php
declare(strict_types=1);


namespace Core\DI;

class DependenciesLoader
{
    public static function get(): array
    {
        $path = dirname(__FILE__, 3) . "/Config/dependencies.json";
        $json = file_get_contents($path);
        return json_decode($json, true);
    }
}