<?php

function array_insert(&$array, $position, $insert) {
    if (is_int($position))
        array_splice($array, $position, 0, $insert);
    else {
        $pos = array_search($position, array_keys($array));
        $array = array_merge(
              array_slice($array, 0, $pos),
              $insert,
              array_slice($array, $pos)
        );
    }
}

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

function getDigitPlace($pattern) {
    $split = str_split($pattern);
    $places = [];
    for ($i = 0; $i < sizeof($split); $i++) {
        if (is_numeric($split[$i]))
            $places[] = $i;
    }
    return $places;
}

function hyphenate($word, $patterns) {
    $remadeword = $word;
    $rchars = str_split($remadeword);

    foreach ($patterns as &$pattern) {
        $pattern = trim(preg_replace('/\s+/', ' ', $pattern));
        $pattern = strval($pattern);
        $pchars = str_split($pattern);

        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof($pchars));

        $patternSpot = strpos($word, $cleanString);
        $digitPlaces = getDigitPlace($pattern);

        if (findStartPattern($patterns) == $pattern) {
            foreach ($digitPlaces as $digit) {
                array_insert($rchars, $digit - 1, $pchars[$digit]);
            }
        }

        if (findEndPattern($patterns) == $pattern) {
            if ($patternSpot == sizeof($rchars) - strlen($cleanString) - 1) {
                foreach ($digitPlaces as $digit) {
                    $placement = sizeof($rchars) - strlen($cleanString) + $digit;
                    array_insert($rchars, $placement, $pchars[$digit]);
                }
            }
        }
        
        if (findEndPattern($patterns) != $pattern && findStartPattern($patterns) != $pattern) {
            foreach ($digitPlaces as $digit) {
                // middle digits
            }
        }

        $remadeword = implode('', $rchars);
    }
    return $remadeword;
}

