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

function getPatternsForWord($word, $patternList) {
    $patterns = [];
    foreach($patternList as $pattern) {
        $pchars = str_split($pattern);

        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof($pchars));

        if (strpos($word, $cleanString) !== false)
            $patterns[] = $pattern;
    }
    return $patterns;
}

function hyphenate($word, $patterns) {
    $chars = str_split($word);

    $logcontent = "";
    
    $remadeword = $word;
    $rchars = str_split($remadeword);

    foreach ($patterns as &$pattern) {
        $pattern = strval($pattern);
        $pchars = str_split($pattern);

        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof($pchars));

        /*     debug
        $logcontent .= "$cleanString / $pattern\n";
        logtofile('log.txt', $logcontent);
        */
        
        $patternKeyPos = null;
        $position = null;

        for ($i = 0; $i < sizeof($pchars); $i++) {
            if (is_numeric($pchars[$i])) {
                $patternKeyPos = $i;
                continue;
            }
            if ($pchars[$i] == '.') {
                if ($i == 0) $position = 'start';
                else if ($i > 0) $position = 'end';
                continue;
            }
        }

        $lengthOfPattern = strlen($pattern);
        $pos = strpos($remadeword, $cleanString);

        if ($pos !== false) {
            switch($position) {
                case 'start': {
                    if ($pos == 0)
                        array_insert($rchars, 0 + $patternKeyPos - 1, $pattern[$patternKeyPos]);
                    break;
                }
                case 'end': {
                    if ($pos == strlen($remadeword))
                        array_insert($rchars, strlen($remadeword) - $patternKeyPos - 1, $pattern[$patternKeyPos]);
                    break;
                }
                default: {
                    $newpos = $pos + $patternKeyPos;
                    if (is_numeric($rchars[$newpos])) {
                        if ($rchars[$newpos] < $pattern[$patternKeyPos])
                            array_insert($rchars, $pos + $patternKeyPos, $pattern[$patternKeyPos]);
                    }
                    else array_insert($rchars, $pos + $patternKeyPos, $pattern[$patternKeyPos]);  
                    break;
                }
            }
            $remadeword = implode('', $rchars);
        }

    }
    return $remadeword;
}

