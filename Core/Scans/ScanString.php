<?php
declare(strict_types=1);

namespace Core\Scans;

use Algorithms\StringHyphenation;
use Core\Application;
use Core\Cache\FileCache;
use Core\Tools;
use SplFileObject;
use Exception;

class ScanString
{
    private $algorithm;
    private $cache;

    public function __construct(StringHyphenation $stringAlgorithm, FileCache $cache)
    {
        $this->algorithm = $stringAlgorithm;
        $this->cache = $cache;

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings), Tools::CACHE_DEFAULT_EXPIRATION, Tools::CACHE_DIR_MODE, Tools::CACHE_FILE_MODE);
    }

    public function hyphenate(string $src): string
    {
        if ($this->cache->has($src)) {
            return (string)$this->cache->get($src);
        } else {
            $string = $this->loadStringFromFile($src);
            $result = $this->algorithm->hyphenate($string);
            $this->cache->set($src, $result);
            return $result;
        }
    }

    private function loadStringFromFile(string $src): string
    {
        $result = "";
        if ($this->isFileExists($src)) {
            $result = $this->getStringFromFile($src);
        }
        return $result;
    }

    private function getStringFromFile(string $src): string
    {
        $return = "";
        try {
            $file = new SplFileObject($src);

            while (!$file->eof())
                $return .= $file->fgets();

        } catch (Exception $e) {
            die("Error while trying to load  file!\n$e");
        }
        return $return;
    }

    private function isFileExists(string $src): bool
    {
        return file_exists($src);
    }
}