<?php
declare(strict_types=1);

namespace Core\Database;

use Core\Cache\FileCache;
use Core\Database\Interfaces\ExportInterface;

class Export implements ExportInterface
{
    private $connection;
    private $cache;
    private static $instance;

    public function __construct()
    {
        $this->connection = Singleton::getInstanceOf();
        $this->cache = FileCache::getInstanceOf();
    }

    public static function getInstanceOf()
    {
        if (!self::$instance) {
            self::$instance = new Export();
        }
        return self::$instance;
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