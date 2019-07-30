<?php
declare(strict_types=1);

namespace NXT\Core;

class Config
{
    public function get(string $configName): array
    {
        $path = $this->getConfigPath($configName);
        $config = json_decode(
            file_get_contents($path),
            true
        );
        return $config;
    }

    public function set(string $configName, array $config): void
    {
        file_put_contents(
            $this->getConfigPath($configName),
            json_encode($config)
        );
    }

    private function getConfigPath(string $name): string
    {
        return dirname(__FILE__, 3) . '/config/' . $name . '.json';
    }
}