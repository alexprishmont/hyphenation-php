<?php

namespace Algorithms\String;

use Algorithms\AlgorithmInterface;
use Algorithms\Hyphenation;

class Stringhyphenation implements AlgorithmInterface
{
    private $algorithm;

    public function __construct(array $patterns)
    {
        $this->algorithm = new Hyphenation($patterns);
    }

    public function hyphenate(string $string): string
    {
        $words = $this->extract_words_from_string($string);
        foreach ($words as $word) {
            $word_with_syllable = $this->algorithm->hyphenate($word);
            var_dump($word_with_syllable);
            $string = str_replace($word, $word_with_syllable, $string);
        }
        return $string;
    }

    private function extract_words_from_string(string $string): array
    {
        $temp = preg_split('/(\s+)/', $this->clear_string($string), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

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

    private function clear_string(string $string): string
    {
        return preg_replace("/[^a-zA-Z]/", " ", $string);
    }
}
