<?php

    /**
     * @param $element
     * @return array
     */
    function cleanString($element) {
        $chars = str_split($element, 1);

        $return = preg_replace("/[^a-zA-Z]/", "", $element);
        $return = substr($return, 0, 8);

        $pos = null;

        for ($i = 0; $i < sizeof($chars); $i++) {
            if ($chars[$i] == ".") {
                if ($i == 0) { // it should be in the beginning of the word
                    $pos = 'start';
                }
                else if ($i == sizeof($chars) - 1) { // it should be in the end of the word
                    $pos = 'end';
                }
            }
        }

        $arr = [
            'element' => $return,
            'position' =>  $pos
        ];

        return $arr;
    }

    function trimSymbols($element) {
        return str_replace(".", "", $element);
    }

    function remakeWord($word, $list = []) {
        $remadeWord = null;
        $changed = false;
        $edited = false;

        foreach($list as &$item) {
            $item = trim(preg_replace('/\s+/', ' ', $item)); // deleting all new lines which are not needed in this case

            $clean = cleanString($item);
            $string = strval($clean['element']);

            if ($clean['position'] == 'start') {
                if (substr($word, 0, strlen($string)) == $string) {
                    $remadeWord = str_replace($string, trimSymbols($item), $word);
                    $edited = true;
                }
            }
            else if ($clean['position'] == null) {
                if ($edited) {
                    $remadeWord = str_replace($string, trimSymbols($item), $remadeWord);
                }
                else {
                    $remadeWord = str_replace($string, trimSymbols($item), $word);
                    $edited = true;
                }
            }
        }
        echo $remadeWord;
        return $remadeWord;
    }

    function findSyllables($remadeWord) {
        $syllables = null;


        return $syllables;
    }