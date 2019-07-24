<?php

namespace Core;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $classFile = $_SERVER['DOCUMENT_ROOT'] .
                str_replace('\\', DIRECTORY_SEPARATOR, $class) .
                '.php';

            if (strpos($classFile, '/Public') !== false)
                $classFile = str_replace('/Public', '/', $classFile);

            if (file_exists($classFile)) {
                require $classFile;
                return true;
            }
        });
    }
}