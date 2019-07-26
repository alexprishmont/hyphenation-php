<?php

namespace NXT\Core\DI\Interfaces;

interface ContainerInterface
{
    public function get(string $name, array $parameters = []);
    
    public function set(string $name, object $objectName = null);
}