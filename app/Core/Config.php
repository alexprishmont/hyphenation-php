<?php
declare(strict_types=1);

namespace NXT\Core;

use NXT\Core\Exceptions\InvalidFlagException;

class Config
{
    public function get(string $configName): array
    {
        $path = $this->getConfigPath($configName);

        $this->validatePath($path);

        $config = json_decode(
            file_get_contents($path),
            true
        );

        $this->validateConfig($config);

        return $config;
    }

    public function set(string $configName, array $config): void
    {
        $this->validatePath($this->getConfigPath($configName));
        $this->validateConfig($config);

        file_put_contents(
            $this->getConfigPath($configName),
            json_encode($config)
        );
    }

    private function validatePath(string $path)
    {
        if (!$this->isConfigExists($path)) {
            throw new InvalidFlagException(
                'Config [' . $path . '] does not exist.'
            );
        }
    }

    private function validateConfig(array $config)
    {
        if (!isset($config) || empty($config)) {
            throw new InvalidFlagException('Config file is empty. Cannot load..');
        }
    }

    private function isConfigExists(string $path): bool
    {
        return file_exists($path);
    }

    private function getConfigPath(string $name): string
    {
        return dirname(__FILE__, 3) . '/config/' . $name . '.json';
    }
}