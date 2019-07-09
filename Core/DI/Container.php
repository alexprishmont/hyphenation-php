<?php

namespace Core\DI;

use Core\DI\Interfaces\ContainerInterface;

class Container implements ContainerInterface
{
    private $services = [];
    private $instances = [];

    public function get(string $name)
    {
    }

    public function has(string $name)
    {
    }

    public function set(string $name, object $objectName)
    {
    }
}