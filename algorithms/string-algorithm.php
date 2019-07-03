<?php


class Stringhyphenation {
    private $string;
    private $cleanString;
    private $words = [];
    private $patterns;
    private $syllableWords = [];

    public function __construct($string, $patterns) { $this->string = $string; $this->patterns = $patterns; }
    public function result() {
        $this->clearString();
        $this->stringToWords();

        $this->hyphenateString();
        $this->resultString();

        echo $this->string;
    }

    private function clearString() { $this->cleanString = preg_replace("/[^a-zA-Z]/", " ", $this->string); }
    private function hyphenateString() {
        foreach ($this->words as $word)
            $this->syllableWords[] = hyphenate($word, getPatternsForWord($word, $this->patterns));
    }
    private function resultString() {
        // TO-DO refactor syllableWords back to string with all symbols.
      /* for ($i = 0; $i < strlen($this->string); $i++)
            $this->string[$i] = $this->syllableWords[$i];*/
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