<?php

namespace NXT\Core\Cache;

class Cache
{
    private $cache;

    public function __construct()
    {
        $this->cache = FileCache::getInstanceOf();
    }

    public function clear()
    {
        $this->cache
            ->clear();
    }
}