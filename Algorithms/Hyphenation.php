<?php

namespace Algorithms;

use Algorithms\Interfaces\AlgorithmInterface;

class Hyphenation implements AlgorithmInterface
{

    private $word;
    private $patterns = [];
    private $valid_patterns = [];
    private $digits_in_word = [];
    private $completed_word_with_digits;

    public function __construct(array $patterns)
    {
        $this->patterns = $patterns;
    }

    public function hyphenate(string $word): string
    {
        $this->clearVariables();

        $this->word = $word;
        $this->findValidPatterns();
        $this->pushDigitsToWord();
        $this->completeWordWithSyllables();
        return $this->addSyllableSymbols();
    }

    private function clearVariables(): void
    {
        $this->word = null;
        $this->completed_word_with_digits = null;
        $this->valid_patterns = [];
        $this->digits_in_word = [];
    }

    private function addSyllableSymbols(): string
    {
        $result = $this->completed_word_with_digits;
        for ($i = 0; $i < strlen($this->completed_word_with_digits); $i++) {
            $c = $this->completed_word_with_digits[$i];
            if (is_numeric($c)) {
                if ((int)$c % 2 > 0) {
                    $result = str_replace($c, '-', $result);
                } else
                    $result = str_replace($c, '', $result);
            }
        }
        return $result;
    }

    private function completeWordWithSyllables(): void
    {
        foreach (str_split($this->word) as $i => $c) {
            $this->completed_word_with_digits .= $c;
            if (isset($this->digits_in_word[$i]))
                $this->completed_word_with_digits .= $this->digits_in_word[$i];
        }
    }

    private function pushDigitsToWord(): void
    {
        foreach ($this->valid_patterns as $pattern) {
            $digits_in_pattern = $this->extractDigitsFromWord($pattern);
            foreach ($digits_in_pattern as $position => $digit) {
                $position = $position + strpos($this->word, $this->clearPatternString($pattern));
                if (!isset($this->digits_in_word[$position]) || $this->digits_in_word[$position] < $digit)
                    $this->digits_in_word[$position] = $digit;
            }
        }
    }

    private function extractDigitsFromWord(string $pattern): array
    {
        $digits = [];
        if (preg_match_all('/[0-9]+/', $pattern, $matches, PREG_OFFSET_CAPTURE) > 0) {
            $offset = preg_match('/[0-9]/', $pattern);
            foreach ($matches[0] as $match) {
                [$digit, $position] = $match;
                $position = $position - $offset;
                $offset = $offset + strlen($digit);
                $digits[$position] = (int)$digit;
            }
        }
        return $digits;
    }

    private function clearPatternString(string $pattern): string
    {
        $clean_string = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $clean_string = substr($clean_string, 0, sizeof(str_split($pattern)));
        return trim(preg_replace("/\s+/", " ", $clean_string));
    }

    private function findValidPatterns(): void
    {
        foreach ($this->patterns as $pattern) {
            $clean_string = $this->clearPatternString($pattern);
            $position = strpos($this->word, $clean_string);

            if ($position === false || ($pattern[0] == '.' && $position !== 0) || ($pattern[strlen($pattern) - 1] == '.' && $position !== strlen($this->word) - strlen($clean_string)))
                continue;

            $this->valid_patterns[] = $pattern;
        }
    }
}