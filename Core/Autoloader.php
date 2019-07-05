<?php

namespace Core;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $class_file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if (file_exists($class_file)) {
                require $class_file;
                return true;
            }
        });
    }
}