<?php

namespace Core\Cache\Interfaces;

interface CacheInterface
{
    public function get($key, $default = null);

    public function set($key, $value, int $ttl = 0);

    public function delete($key);

    public function clear();

    public function getMultiple($keys, $default = null);

    public function setMultiple($values, int $ttl = 0);

    public function deleteMultiple($keys);

    public function has($key);
}