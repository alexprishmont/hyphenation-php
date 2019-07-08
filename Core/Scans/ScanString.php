<?php
declare(strict_types = 1);

namespace Core\Scans;

use Algorithms\Stringhyphenation;
use SplFileObject;
use Exception;

class ScanString
{
    private $fileSrc;

    private $algorithm;

    public function __construct(Stringhyphenation $stringAlgorithm)
    {
        $this->algorithm = $stringAlgorithm;
    }

    public function inputSrc(string $src): void
    {
        $this->fileSrc = $src;
    }
    public function result(): string
    {
        $string = $this->loadStringFromFile();
        return $this->algorithm->hyphenate($string);
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