<?php
declare(strict_types=1);

namespace Core\Scans;

use Core\Application;
use Core\Cache\FileCache;
use Core\Tools;
use SplFileObject;

class Scan
{
    private $cache;

    public function __construct(FileCache $cache)
    {
        $this->cache = $cache;
        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings), Tools::CACHE_DEFAULT_EXPIRATION, Tools::CACHE_DIR_MODE, Tools::CACHE_FILE_MODE);
    }

    public function readDataFromFile(string $src): array
    {
        $src = dirname(__FILE__, 3) . Application::$settings['INPUT_SRC'] . '/' . $src;

        if (!$this->cache->has("patterns")) {
            $file = new SplFileObject($src);
            $data = [];
            foreach ($file as $data_num => $data_c) {
                $data[] = $data_c;
            }
            $this->cache->set("patterns", $data);
            return $data;
        } else {
            return $this->cache->get("patterns");
        }
    }
}