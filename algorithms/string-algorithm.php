<?php


class Stringhyphenation {
    private $string;
    private $cleanString;
    private $words = [];

    public function __construct($string) {
        $this->string = $string;
        $this->cleanString = $this->clearString();
        echo $this->cleanString;
    }

    private function clearString() {
        return preg_replace("//", "", $this->string);
    }

    private function stringToWords() {

    }

}