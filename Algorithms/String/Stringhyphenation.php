<?php
namespace Algorithms\String;
class Stringhyphenation extends \Algorithms\Hyphenation implements \Algorithms\Algorithm {
    private $string;
    private $cleanString;
    private $words = [];
    private $patterns;

    public function __construct($string, $patterns) {
        $this->string = $string; $this->patterns = $patterns;
    }
    public function hyphenate():string {
        $this->clearString();
        $this->stringToWords();
        return $this->hyphenateString();
    }

    private function clearString() { $this->cleanString = preg_replace("/[^a-zA-Z]/", " ", $this->string); }
    private function hyphenateString() {
        foreach ($this->words as $word) {
            parent::__construct($word, $this->patterns);
            $word_with_syllables = parent::hyphenate();
            var_dump($word_with_syllables);
            $this->string = str_replace($word, $word_with_syllables, $this->string);
        }
        return $this->string;
    }
    private function stringToWords() {
        $temp = preg_split('/(\s+)/', $this->cleanString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $spaces = [];
        $this->words = array_reduce($temp, function(&$result, $item) use (&$spaces) {
            if (strlen(trim($item)) === 0)
                $spaces[] = strlen($item);
            else
                $result[] = $item;
            return $result;
        }, []);
    }

}