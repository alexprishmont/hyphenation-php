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



function hyphenate($word, $patterns) {
    $chars = str_split($word);

    $remadeword = $word;
    $rchars = str_split($remadeword);

    foreach ($patterns as &$pattern) {
        $pattern = strval($pattern);
        $pchars = str_split($pattern);

        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof($pchars));

        $patternKeyPos = null;
        $position = null;

        for ($i = 0; $i < sizeof($pchars); $i++) {
            if (is_numeric($pchars[$i])) {
                $patternKeyPos = $i;
                continue;
            }
            if ($pchars[$i] == '.') {
                if ($i == 0) $position = 'start';
                else if ($i == sizeof($pchars) - 1) $position = 'end';
                continue;
            }
        }

        $lengthOfPattern = strlen($pattern);
        $pos = strpos($remadeword, $cleanString);
        if ($pos !== false) {
            if ($position == 'start' && $pos == 0) {
                array_insert($rchars, 0 + $patternKeyPos, $pattern[$patternKeyPos]);
            }
            else if ($position == 'end' && $pos == strlen($word)) {
                array_insert($rchars, sizeof($rchars) - $patternKeyPos, $pattern[$patternKeyPos]);
            }
            else {
                array_insert($rchars, $pos + $patternKeyPos, $pattern[$patternKeyPos]);                                   
            }
            $remadeword = implode('', $rchars);
        }

    }
    return $remadeword;
}

