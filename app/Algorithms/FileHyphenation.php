<?php
declare(strict_types=1);

namespace NXT\Algorithms;

use NXT\Algorithms\Interfaces\HyphenationInterface;
use NXT\Application;
use NXT\Core\Exceptions\InvalidFlagException;

class FileHyphenation implements HyphenationInterface
{
    private $algorithm;

    public function __construct(StringHyphenation $stringAlgorithm)
    {
        $this->algorithm = $stringAlgorithm;
    }

    public function hyphenate(string $src): string
    {
        $src = dirname(__FILE__, 2) . Application::$settings['INPUT_SRC'] . '/' . $src;
        if ($this->isFileExists($src)) {
            $array = $this->getArrayFromFile($src);
            $array = implode(' ', $array);
            $result = $this->algorithm->hyphenate($array);
            return $result;
        }
        throw new InvalidFlagException('Your entered file does not exist. [' . $src . ']');
    }

    private function getArrayFromFile(string $src): array
    {
        $return = file_get_contents($src);
        $return = preg_split('/\s+/', $return);
        return $return;
    }

    private function isFileExists(string $src): bool
    {
        return file_exists($src);
    }
}