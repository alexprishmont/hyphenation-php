<?php

namespace Core\Cache;

use Core\Application;
use Core\Tools;

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