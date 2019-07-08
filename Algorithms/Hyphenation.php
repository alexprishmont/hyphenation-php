<?php
declare(strict_types=1);

namespace Algorithms;

use Algorithms\Interfaces\AlgorithmInterface;

class Hyphenation implements AlgorithmInterface
{

    private $word;
    private $patterns = [];
    private $validPatterns = [];
    private $digitsInWord = [];
    private $completedWordWithDigits;

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
        $this->completedWordWithDigits = null;
        $this->validPatterns = [];
        $this->digitsInWord = [];
    }

    private function addSyllableSymbols(): string
    {
        $result = $this->completedWordWithDigits;
        for ($i = 0; $i < strlen($this->completedWordWithDigits); $i++) {
            $c = $this->completedWordWithDigits[$i];
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
            $this->completedWordWithDigits .= $c;
            if (isset($this->digitsInWord[$i]))
                $this->completedWordWithDigits .= $this->digitsInWord[$i];
        }
    }

    private function pushDigitsToWord(): void
    {
        foreach ($this->validPatterns as $pattern) {
            $digits_in_pattern = $this->extractDigitsFromWord($pattern);
            foreach ($digits_in_pattern as $position => $digit) {
                $position = $position + strpos($this->word, $this->clearPatternString($pattern));
                if (!isset($this->digits_in_word[$position]) || $this->digitsInWord[$position] < $digit)
                    $this->digitsInWord[$position] = $digit;
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
        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof(str_split($pattern)));
        return trim(preg_replace("/\s+/", " ", $cleanString));
    }

    private function findValidPatterns(): void
    {
        foreach ($this->patterns as $pattern) {
            $cleanString = $this->clearPatternString($pattern);
            $position = strpos($this->word, $cleanString);

            if ($position === false || ($pattern[0] == '.' && $position !== 0) ||
                ($pattern[strlen($pattern) - 1] == '.' && $position !== strlen($this->word) - strlen($cleanString)))
                continue;

            $this->validPatterns[] = $pattern;
        }
    }
}