<?php

namespace Core\DI\Interfaces;

interface ContainerInterface
{
    public function get(string $name);

    public function has(string $name);

    public function set(string $name, object $objectName);
}