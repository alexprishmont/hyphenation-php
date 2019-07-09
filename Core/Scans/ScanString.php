<?php
declare(strict_types=1);

namespace Core\Scans;

use Algorithms\Stringhyphenation;
use Core\Cache\FileCache;
use SplFileObject;
use Exception;

class ScanString
{
    private $fileSrc;

    private $algorithm;
    private $cache;

    public function __construct(Stringhyphenation $stringAlgorithm, FileCache $cache)
    {
        $this->algorithm = $stringAlgorithm;
        $this->cache = $cache;
    }

    public function inputSrc(string $src): void
    {
        $this->fileSrc = $src;
    }

    public function result(): string
    {
        if ($this->cache->has($this->fileSrc)) {
            return (string)$this->cache->get($this->fileSrc);
        } else {
            $string = $this->loadStringFromFile();
            $result = $this->algorithm->hyphenate($string);
            $this->cache->set($this->fileSrc, $result);
            return $result;
        }
    }

    private function loadStringFromFile(): string
    {
        $result = "";
        if ($this->isFileExists()) {
            $result = $this->getStringFromFile();
        }
        return $result;
    }

    private function getStringFromFile(): string
    {
        $return = "";
        try {
            $file = new SplFileObject($this->fileSrc);

            while (!$file->eof())
                $return .= $file->fgets();

        } catch (Exception $e) {
            die("Error while trying to load  file!\n$e");
        }
        return $return;
    }

    private function isFileExists(): bool
    {
        return file_exists($this->fileSrc);
    }
}