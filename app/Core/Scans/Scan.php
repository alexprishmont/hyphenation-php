<?php
declare(strict_types=1);

namespace NXT\Core\Scans;

use NXT\Core\Application;
use NXT\Core\Cache\FileCache;
use NXT\Core\Scans\Interfaces\ScanInterface;
use SplFileObject;

class Scan implements ScanInterface
{
    private $cache;

    public function __construct()
    {
        $this->cache = FileCache::getInstanceOf();
    }

    public function readDataFromFile(string $src): array
    {
        $src = dirname(__FILE__, 3) . Application::$settings['INPUT_SRC'] . '/' . $src;

        if (!$this->cache->has('patterns')) {
            $file = new SplFileObject($src);
            $data = [];
            foreach ($file as $data_num => $data_c) {
                $data_c = trim(preg_replace('/\s\s+/', ' ', $data_c));
                $data[] = $data_c;
            }
            $this->cache->set('patterns', $data);
            return $data;
        } else {
            return $this->cache->get('patterns');
        }
    }
}