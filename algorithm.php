<?php

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


function hyphenate($word, $patterns) {
    foreach ($patterns as &$pattern) {
        $pattern = trim(preg_replace('/\s+/', ' ', $pattern)); // deleting all new lines which are not needed in this case
        $cleaned = cleanString($pattern);

        $cleanedString = strval($cleaned['element']);
        $stringPosition = strval($cleaned['position']);

        $edited = false;
        $remadeWord = null;

        $position = strpos($word, $cleanedString);
        if ($position !== false) {
            if ($stringPosition == "start") {
                if ($position == 0) {
                    if (!$edited) {
                        str_replace($cleanedString, trimSymbols($pattern), $word);
                        $edited = true;
                    }
                    else {
                        if (strpos($remadeWord, $cleanedString) !== false) {
                            // TO-DO
                            // If key digit is greater then add it, else nop.
                        }
                        else
                            str_replace($cleanedString, trimSymbols($pattern), $remadeWord);
                    }
                }
            }
        }

    }
}

