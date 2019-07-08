<?php

namespace Core\Cache;

use Core\Cache\Interfaces\CacheInterface;
use Core\Exceptions\InvalidArgumentException;

class FileCache implements CacheInterface
{
    private const PSR16_RESERVED = '/\{|\}|\(|\)|\/|\\\\|\@|\:/u';

    private $cachePath;

    public function get($key, $default = null)
    {
    }

    public function set($key, $value, $ttl = null)
    {
    }

    public function delete($key)
    {
    }

    public function clear()
    {
    }

    public function getMultiple($keys, $default = null)
    {
    }

    public function setMultiple($values, $ttl = null)
    {
    }

    public function deleteMultiple($keys)
    {
    }

    public function has($key)
    {
    }

    private function getTime()
    {
        return time();
    }

    private function getPath($key)
    {
        $this->validateKey($key);

        $hash = hash("sha256", $key);

        return $this->$cachePath
            . DIRECTORY_SEPARATOR
            . strtoupper($hash[0])
            . DIRECTORY_SEPARATOR
            . strtoupper($hash[1])
            . DIRECTORY_SEPARATOR
            . substr($hash, 2);
    }

    private function validateKey($key)
    {
        if (!is_string($key)) {
            $type = is_object($key) ? get_class($key) : gettype($key);
            throw new InvalidArgumentException("invalid key type: {$type} given");
        }

        if ($key === "")
            throw new InvalidArgumentException("invalid key: empty string given");

        if ($key === null)
            throw new InvalidArgumentException("invalid key: null given");

        if (preg_match(self::PSR16_RESERVED, $key, $match) === 1)
            throw new InvalidArgumentException("invalid character in key: {$match[0]}");
    }
}