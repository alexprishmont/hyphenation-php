<?php
declare(strict_types=1);

namespace Algorithms;

use Algorithms\Interfaces\AlgorithmInterface;

class StringHyphenation implements AlgorithmInterface
{
    private $algorithm;

    public function __construct(Hyphenation $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function hyphenate(string $string): string
    {
        $words = $this->extractWordsFromString($string);
        $result = $string;

        $imploded = implode(' ', $words);
        $implodedResult = $this->algorithm->hyphenate($imploded);

        $hyphendWords = preg_split('/(\s+)/', $implodedResult);

        $fixed = [];
        $i = 0;
        foreach ($hyphendWords as $word) {
            if (strpos($word, '-') === 0 || strpos($word, '-') === 0)
                $word = str_replace('-', '', $word);
            $fixed[$words[$i]] = $word;
            $i++;
        }

        foreach ($fixed as $key => $value) {
            $result = str_replace($key, $value, $result);
        }

        return $result;
    }

    private function extractWordsFromString(string $string): array
    {
        $temp = preg_split('/(\s+)/', $this->clearString($string), -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $spaces = [];
        $words = array_reduce($temp, function (&$result, $item) use (&$spaces) {
            if (strlen(trim($item)) === 0)
                $spaces[] = strlen($item);
            else
                $result[] = $item;
            return $result;
        }, []);
        return $words;
    }

    private function clearString(string $string): string
    {
        return preg_replace("/[^a-zA-Z]/", " ", $string);
    }
}
