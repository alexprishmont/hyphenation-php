<?php
namespace Algorithms\String;
class Stringhyphenation extends \Algorithms\Hyphenation implements \Algorithms\Algorithm {
    private $words = [];
    private $patterns = [];
    private $string;

    public function __construct($patterns, $word = null, $string = null) {
        parent::__construct($patterns, $word, $string);
        $this->patterns = $patterns;
        if (isset($string)) {
            $this->words = $this->extract_words_from_string($string);
            $this->string = $string;
        }
    }

    public function hyphenate(): string {
        foreach ($this->words as $word) {
            parent::__construct($this->patterns, $word);
            $word_with_syllables = parent::hyphenate();
            parent::__destruct();
            $this->string = str_replace($word, $word_with_syllables, $this->string);
        }
        return $this->string;
    }

    private function clear_string(string $string):string {
        return preg_replace("/[^a-zA-Z]/", " ", $string);
    }

    private function extract_words_from_string(string $string):array {
        $words = [];
        $temp = preg_split('/(\s+)/', $this->clear_string($string), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $spaces = [];
        $words = array_reduce($temp, function(&$result, $item) use (&$spaces) {
            if (strlen(trim($item)) === 0)
                $spaces[] = strlen($item);
            else
                $result[] = $item;
            return $result;
        }, []);
        return $words;
    }
}