<?php
declare(strict_types=1);

namespace Algorithms;

use Algorithms\Interfaces\HyphenationInterface;

class Hyphenation implements HyphenationInterface
{
    private $validPatterns = [];
    private $patterns = [];

    public function __construct(array $patternsList)
    {
        $this->patterns = $patternsList;
    }

    public function hyphenate(string $word): string
    {
        return $this->getResult($word);
    }

    public function getValidPatternsForWord(string $word): array
    {
        $valid = $this->findValidPatterns($this->patterns, $word);
        return $valid;
    }

    public function getResult(string $word): string
    {
        $patterns = $this->patterns;
        $this->validPatterns = $this->findValidPatterns($patterns, $word);

        $result = $this->addSyllableSymbols(
            $this->completeWordWithDigits(
                $word, $this->pushDigitsToWord($this->validPatterns, $word)
            )
        );
        return $result;
    }

    private function addSyllableSymbols(string $completedWordWithDigits): string
    {
        $result = $completedWordWithDigits;
        for ($i = 0; $i < strlen($completedWordWithDigits); $i++) {
            $char = $completedWordWithDigits[$i];

            if (!is_numeric($char))
                continue;

            if ((int)$char % 2 > 0) {
                $result = str_replace($char, '-', $result);
            } else {
                $result = str_replace($char, '', $result);
            }
        }
        return $result;
    }

    private function completeWordWithDigits(string $word, array $digitsInWord = []): string
    {
        $completedWordWithDigits = "";
        foreach (str_split($word) as $i => $char) {
            $completedWordWithDigits .= $char;
            if (isset($digitsInWord[$i])) {
                $completedWordWithDigits .= $digitsInWord[$i];
            }
        }
        return $completedWordWithDigits;
    }

    private function pushDigitsToWord(array $validPatterns, string $word): array
    {
        $digitsInWord = [];
        foreach ($validPatterns as $pattern) {
            $digitsInPattern = $this->extractDigitsFromWord($pattern);
            foreach ($digitsInPattern as $position => $digit) {
                $position = $position + strpos($word, $this->clearPatternString($pattern));
                if (!isset($digitsInWord[$position]) || $digitsInWord[$position] < $digit) {
                    $digitsInWord[$position] = $digit;
                }
            }
        }
        return $digitsInWord;
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
        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof(str_split($pattern)));
        return trim(preg_replace("/\s+/", " ", $cleanString));
    }

    private function findValidPatterns(array $patterns, string $word): array
    {
        $validPatterns = [];
        foreach ($patterns as $pattern) {
            $cleanString = $this->clearPatternString($pattern);
            $position = strpos($word, $cleanString);

            if ($position === false ||
                ($pattern[0] == '.' && $position !== 0) ||
                ($pattern[strlen($pattern) - 1] == '.' && $position !== strlen($word) - strlen($cleanString))) {
                continue;
            }
            $validPatterns[] = $pattern;
        }
        return $validPatterns;
    }
}