<?php

function findStartPattern(&$array = []) {
    foreach ($array as $item) {
        $chars = str_split($item);
        for ($i = 0; $i < sizeof($chars); $i++) {
            if ($i == 0 && $chars[$i] == '.')
                return $item;
        }
    }
    return false;
}

function findEndPattern(&$array = []) {
    foreach ($array as $item) {
        $chars = str_split($item);
        for ($i = 0; $i < sizeof($chars); $i++) {
            if ($i > 0 && $chars[$i] == '.')
                return $item;
        }
    }
    return false;
}

function getPatternsForWord($word, $patternList) {
    $patterns = [];

    foreach($patternList as $pattern) {
        $pchars = str_split($pattern);

        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof($pchars));

        $foundPosition = strpos($word, $cleanString);
        if ($foundPosition !== false) {
            $search = strval(array_search('.', $pchars));
            if (array_key_exists($search, $pchars)) {
                if ($search == 0 && !findStartPattern($patterns)) {
                    $wordSection = substr($word, 0, strlen($cleanString));
                    if ($wordSection == $cleanString)
                        $patterns[] = $pattern;
                }
                else if ($search > 0 && !findEndPattern($patterns)) {
                    $wordSection = substr($word,strlen($word) - strlen($cleanString), strlen($word));
                    if ($wordSection == $cleanString)
                        $patterns[] = $pattern;
                }
            }
            else $patterns[] = $pattern;
        }
    }
    return $patterns;
}

function getResult($pattern, $pos, $cleanString) {
    $chars = [];
    $chardigits = [];
    $enddigits = [];

    preg_match_all('/[0-9]+[a-z]{1}/', $pattern, $chars);
    preg_match_all('/[0-9]+$/', $pattern, $enddigit);

    foreach ($chars as $x => $y) {
        foreach ($y as $char) {
            $c = preg_replace('/[0-9]+/', '', $char);
            $n = intval(preg_replace('/[a-z]{1}/', '', $char));
            $chardigits[$c] = $n;
        }
    }

    foreach ($enddigits as $x => $y) {
        foreach($y as $char) {
            $chardigits[''] = intval($char);
        }
    }

    return [
        'position' => $pos,
        'char_digits' => $chardigits,
        'length' => strlen($cleanString)
    ];
}

function getPatternsStruct($word, $patterns) {
    $patterns_struct = [];
    foreach ($patterns as $pattern) {
        $cleanString = preg_replace('/[0-9]+/', '', $pattern);
        $cleanString = trim(preg_replace('/\s+/', ' ', $cleanString));

        if (findStartPattern($patterns) == $pattern) {
            // beginning
            $position = strpos($word, substr($cleanString, 1));
            if ($position === 0)
                $patterns_struct[] = getResult(str_replace('.', '', $pattern), $position, str_replace('.', '', $cleanString));
        }
        if (findStartPattern($patterns) != $pattern && findEndPattern($patterns) == $pattern) {
            // end
            $position = strpos($word, substr($cleanString, 0, strlen($cleanString) - 1));
            if ($position === strlen($word) - strlen($cleanString) + 1)
                $patterns_struct[] = getResult(str_replace('.', '', $pattern), $position, str_replace('.', '', $cleanString));
        }
        if (findEndPattern($patterns) != $pattern && findStartPattern($patterns) != $pattern) {
            // middle
            $position = strpos($word, $cleanString);
            if ($position !== false)
                $patterns_struct[] = getResult(str_replace('.', '', $pattern), $position, str_replace('.', '', $cleanString));
        }
    }

    return $patterns_struct;
}

function getWordStruct($word) {
    $struct = [];
    for ($i = 0; $i < strlen($word); $i++) {
        $struct[] = [
            'char' => $word[$i],
            'digit' => 0
        ];
    }
    return $struct;
}

function makeWordWithSyllables($word_struct) {
    $minus_count = 0;
    foreach ($word_struct as $char_struct) {
        $char = $char_struct['char'];
        $digit = $char_struct['digit'];
        if (!empty($digit)) {
            if ($digit % 2 > 0) {
                if ($minus_count > 0)
                    echo '-';
            }
        }
        echo $char;
        $minus_count ++;
    }
}

function hyphenate($word, $patterns) {
    $struct = getWordStruct($word);
    $patterns_struct = getPatternsStruct($word, $patterns);

    foreach($patterns_struct as $pattern_struct) {
        $position = $pattern_struct['position'];
        $digits = $pattern_struct['char_digits'];

        for ($i = $position; $i < $position + $pattern_struct['length']; $i++) {
            if (isset($struct[$i])) {
                $char = $struct[$i]['char'];
                if (isset($digits[$char])) {
                    $digit = $digits[$char];
                    if ($digit > $struct[$i]['digit'])
                        $struct[$i]['digit'] = $digit;
                }
            }
        }

        if (isset($digits[''])) {
            $index = $position + $pattern_struct['length'];
            if (isset($struct[$index])) {
                $digit = $digits[''];
                if ($digit > $struct[$index]['digit'])
                    $struct[$index]['digit'] = $digit;
            }
        }
    }
    return makeWordWithSyllables($struct);
}