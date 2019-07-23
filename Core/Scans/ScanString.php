<?php
declare(strict_types=1);

namespace Core\Scans;

use Algorithms\StringHyphenation;
use Core\Application;
use Core\Cache\FileCache;
use Core\Database\Connection;
use Core\Exceptions\InvalidFlagException;
use Core\Tools;

class ScanString
{
    private $algorithm;
    private $cache;

    public function __construct(StringHyphenation $stringAlgorithm, FileCache $cache)
    {
        $this->algorithm = $stringAlgorithm;
        $this->cache = $cache;

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings),
            Tools::CACHE_DEFAULT_EXPIRATION,
            Tools::CACHE_DIR_MODE,
            Tools::CACHE_FILE_MODE
        );
    }

    public function hyphenate(string $src): string
    {
        $src = Application::$settings['INPUT_SRC'] . '/' . $src;
        if ($this->isFileExists($src)) {
            $result = "";
            if ($this->cache->has($src)) {
                return (string)$this->cache->get($src);
            } else {
                $chunks = $this->getChunksFromArray($src);
                foreach ($chunks as $key => $value) {
                    $implodedChunks = implode(' ', $value);
                    $result .= $this->algorithm->hyphenate($implodedChunks);
                }
                $this->cache->set($src, $result);
                return $result;
            }
        } else {
            throw new InvalidFlagException("Your entered file does not exist. [{$src}]");
        }
    }

    private function getChunksFromArray(string $src): array
    {
        $return = file_get_contents($src);
        $return = preg_split('/\s+/', $return);

        $chunks = array_chunk($return, 100);
        return $chunks;
    }

    private function isFileExists(string $src): bool
    {
        return file_exists($src);
    }
}