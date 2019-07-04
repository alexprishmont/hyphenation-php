<?php
namespace Algorithm;

class Wordhyphenation implements Algorithm {

    private $word;
    private $patterns = [];
    private $valid_patterns = [];
    private $digits_in_word = [];
    private $completed_word_with_digits;

    public function __construct($word, $patterns) { $this->word = $word; $this->patterns = $patterns; }
    public function hyphenate():string {
        $this->find_valid_patterns();
        $this->push_digits_to_word();
        $this->complete_word_with_digits();
        return $this->add_syllable_symbols();
    }

    private function add_syllable_symbols():string {
        $result = $this->completed_word_with_digits;
        for ($i = 0; $i < strlen($this->completed_word_with_digits); $i++) {
            $c = $this->completed_word_with_digits[$i];
            if (is_numeric($c)) {
                if ((int)$c % 2 > 0) {
                    $result = str_replace($c, '-', $result);
                }
                else
                    $result = str_replace($c, '', $result);
            }
        }
        return $result;
    }

    private function complete_word_with_digits():void {
        foreach (str_split($this->word) as $i => $c) {
            $this->completed_word_with_digits .= $c;
            if (isset($this->digits_in_word[$i]))
                $this->completed_word_with_digits .= $this->digits_in_word[$i];
        }
    }

    private function push_digits_to_word():void {
        foreach ($this->valid_patterns as $pattern) {
            $digits_in_pattern = $this->extract_digits_from_pattern($pattern);
            foreach ($digits_in_pattern as $position => $digit) {
                $position = $position + strpos($this->word, $this->clear_pattern_string($pattern));
                if (!isset($this->digits_in_word[$position]) || $this->digits_in_word[$position] < $digit)
                    $this->digits_in_word[$position] = $digit;
            }
        }
    }

    private function extract_digits_from_pattern(string $pattern):array {
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

    private function clear_pattern_string(string $pattern):string {
        $clean_string = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $clean_string = substr($clean_string, 0, sizeof(str_split($pattern)));
        return trim(preg_replace("/\s+/", " ", $clean_string));
    }

    private function find_valid_patterns():void {
        foreach ($this->patterns as $pattern) {
            $clean_string = $this->clear_pattern_string($pattern);
            $position = strpos($this->word, $clean_string);

            if ($position === false || ($pattern[0] == '.' && $position !== 0) || ($pattern[strlen($pattern) - 1] == '.' && $position !== strlen($this->word) - strlen($clean_string)))
                continue;

            $this->valid_patterns[] = $pattern;
        }
    }
}