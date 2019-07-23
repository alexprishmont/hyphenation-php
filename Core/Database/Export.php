<?php
declare(strict_types=1);

namespace Core\Database;

use Core\Application;
use Core\Cache\FileCache;
use Core\Tools;

class Export
{
    private $connection;
    private $cache;

    public function __construct(FileCache $cache)
    {
        $this->connection = Singleton::getInstanceOf();
        $this->cache = $cache;

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings),
            Tools::CACHE_DEFAULT_EXPIRATION,
            Tools::CACHE_DIR_MODE,
            Tools::CACHE_FILE_MODE
        );
    }

    public function extractPatternsFromDatabase()
    {
        if ($this->cache->has("patterns")) {
            return $this->cache->get("patterns");
        }

        $fetch = $this->connection
            ->getHandle()
            ->query("select pattern from `patterns`");
        $fetch = $fetch->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];

        foreach ($fetch as $data) {
            $result[] = $data['pattern'];
        }

        $this->cache->set("patterns", $result);
        return $result;
    }
}