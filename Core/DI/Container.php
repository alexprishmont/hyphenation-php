<?php

namespace Core\DI;

use Core\DI\Interfaces\ContainerInterface;

class Container implements ContainerInterface
{
    private $instances = [];
    
    public function get(string $name, array $parameters = [])
    {
        if (!isset($this->instances[$name]))
            $this->set($name);
        return $this->resolve($this->instances[$name], $parameters);
    }

    public function set(string $name, object $objectName = null)
    {
        if ($objectName === null)
            $objectName = $name;

        $this->instances[$name] = $objectName;
    }

    private function resolve(string $object, array $parameters)
    {
        if ($object instanceof \Closure)
            return $object($this, $parameters);

        $reflector = new \ReflectionClass($object);

        if (!$reflector->isInstantiable())
            throw new \Exception("Class {$object} is not instantiable");

        $constructor = $reflector->getConstructor();

        if (is_null($constructor))
            return $reflector->newInstance();

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    private function getDependencies(array $parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();
            if ($dependency === null) {
                if ($parameter->isDefaultValueAvailable())
                    $dependencies[] = $parameter->getDefaultValue();
                else
                    throw new \Exception("Cannot resolve class {$parameter->name} dependencies.");
            } else {
                $dependencies[] = $this->get($dependency->name);
            }
        }

        return $dependencies;
    }
}