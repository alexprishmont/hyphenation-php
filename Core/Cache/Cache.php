<?php

namespace Core\Cache;

use Core\Application;
use Core\Tools;

class Cache
{
    private $cache;

    public function __construct(FileCache $cache)
    {
        $this->cache = $cache;
        $this->cache
            ->setup(Tools::getDefaultCachePath(Application::$settings),
                Tools::CACHE_DEFAULT_EXPIRATION,
                Tools::CACHE_DIR_MODE,
                Tools::CACHE_FILE_MODE);
    }

    public function clear()
    {
        $this->cache
            ->clear();
    }
}