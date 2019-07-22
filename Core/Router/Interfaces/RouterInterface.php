<?php
declare(strict_types=1);

namespace Core\Router\Interfaces;

interface RouterInterface
{
    public static function add($expression, $function, string $method = 'get'): void;

    public static function pathNotFound($function): void;

    public static function methodNotAllowed($function): void;

    public static function run(string $basepath): void;
}