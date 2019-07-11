<?php
namespace Core\Database;

class DatabaseSettings
{
    public static function get(): array
    {
        $path = dirname(__DIR__, 2) . "/Config/database.json";
        return json_decode(file_get_contents($path), true);
    }
}