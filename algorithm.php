<?php

/**
 * @param array $array
 * @return bool|mixed
 * Function finds pattern which should be in the beginning of the word
 * Returns false if there's no such pattern
 * Return full pattern [string] if it found
 */
function findStartPattern(&$array = []) {
    foreach ($array as $item) {
        $chars = str_split($item);
        if($chars[0] == '.') {
            return $item;
        }
    }
    return false;
}

/**
 * @param array $array
 * @return bool|mixed
 * Function finds pattern which should be in the end of the word
 * Returns false if there's no such pattern
 * Return full pattern [string] if it found
 */
function findEndPattern(&$array = []) {
    foreach ($array as $item) {
        $chars = str_split($item);
        if ($chars[sizeof($chars) - 1] == '.')
            return $item;
    }
    return false;
}

/**
 * @param $pattern
 * @return bool|string|string[]|null
 * Cleans pattern string
 * Leaves only chars w/o any digits and other symbols
 */
function getCleanPatternString($pattern) {
    $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
    $cleanString = substr($cleanString, 0, sizeof(str_split($pattern)));
    $cleanString = trim(preg_replace('/\s+/', ' ', $cleanString));
    return $cleanString;
}

/**
 * @param $word
 * @param $patternList
 * @return array
 * Sorts given patterns list
 * Returns patterns which could be used for the given word
 */

function getPatternsForWord($word, $patternList) {
    $patterns = [];
    foreach($patternList as $pattern) {
        $pchars = str_split($pattern);
        $cleanString = getCleanPatternString($pattern);
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

/**
 * @param $pattern
 * @param $pos
 * @param $cleanString
 * @return array
 * Saves data to pattern 2D array
 */
function savePattern($pattern, $pos, $cleanString) {
    $chars = [];
    $chardigits = [];
    $enddigits = [];

    preg_match_all('/[0-9]+[a-z]{1}/', $pattern, $chars);
    preg_match_all('/[0-9]+$/', $pattern, $enddigits);

    foreach ($chars as $x => $y) {
        foreach ($y as $char) {
            $c = preg_replace('/[0-9]+/', '', $char);
            $n = intval(preg_replace('/[a-z]{1}/', '', $char));
            $chardigits[$c] = $n;
        }
    }

    foreach ($enddigits as $x => $y) {
        foreach($y as $char)
            $chardigits[''] = intval($char);
    }

    return [
        'position' => $pos,
        'char_digits' => $chardigits,
        'length' => strlen($cleanString)
    ];
}

/**
 * @param $struct
 * @param $word
 * @param $withoutdot
 * @param $cleanString
 */
function getPatternPositionAtStart(&$struct, $word, $withoutdot, $cleanString) {
    // beginning
    $position = strpos($word, substr($cleanString, 1));
    if ($position === 0)
        $struct[] = savePattern($withoutdot, $position, $cleanString);
}

/**
 * @param $struct
 * @param $word
 * @param $withoutdot
 * @param $cleanString
 */
function getPatternPositionAtEnd(&$struct, $word, $withoutdot, $cleanString) {
    // end
    $position = strpos($word, substr($cleanString, 0, strlen($cleanString) - 1));
    if ($position === strlen($word) - strlen($cleanString) + 1)
        $struct[] = savePattern($withoutdot, $position, $cleanString);
}

/**
 * @param $struct
 * @param $word
 * @param $withoutdot
 * @param $cleanString
 */
function getPatternPositionAtMiddle(&$struct, $word, $withoutdot, $cleanString) {
    // middle
    $position = strpos($word, $cleanString);
    if ($position !== false)
        $struct[] = savePattern($withoutdot, $position, $cleanString);
}
/**
 * @param $word
 * @param $patterns
 * @return array
 * Makes pattern struct using function savePattern($pattern, $pos, $cleanString)
 */
function getPatternsStruct($word, $patterns) {
    $patterns_struct = [];
    foreach ($patterns as $pattern) {
        $cleanString = getCleanPatternString($pattern);
        $patternWithoutDot = str_replace('.', '', $pattern);

        if (findStartPattern($patterns) == $pattern)
            getPatternPositionAtStart($patterns_struct, $word, $patternWithoutDot, $cleanString);
        if (findStartPattern($patterns) != $pattern && findEndPattern($patterns) == $pattern)
            getPatternPositionAtEnd($patterns_struct, $word, $patternWithoutDot, $cleanString);
        if (findEndPattern($patterns) != $pattern && findStartPattern($patterns) != $pattern)
            getPatternPositionAtMiddle($patterns_struct, $word, $patternWithoutDot, $cleanString);

    }
    return $patterns_struct;
}

/**
 * @param $word
 * @return array
 * Makes 2D array for the given word, splits by chars and inserts zeros
 */
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

/**
 * @param $word_struct
 * Final function which splits word to syllables
 */
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

/**
 * @param $word
 * @param $patterns
 * Main function which connects all other functions and returns result
 */
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

    $wordWithSyllables = makeWordWithSyllables($struct);
    return $wordWithSyllables;
}