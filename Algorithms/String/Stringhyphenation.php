<?php
namespace Algorithms\String;
use Algorithms\Hyphenation;

class Stringhyphenation extends Hyphenation {
    private $words = [];
    private $patterns = [];
    private $string;

    public function __construct(array $patterns, string $word = null, string $string = null) {
        parent::__construct($patterns, $word, $string);
        $this->patterns = $patterns;
        if (isset($string)) {
            $this->words = $this->extract_words_from_string($string);require_once("Core/Autoloader.php");
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