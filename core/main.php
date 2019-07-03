<?php
    require_once("debug.php"); // logtofile function

    require_once("scan.php");

    require_once("algorithms/word-algorithm.php");
    require_once("algorithms/string-algorithm.php");

    function loadAlgorithm($option, $target) {
        $start_timing = microtime(true);
        $path = dirname(__FILE__, 2);
        $patternsList = getTextFileData($path."/tex-hyphenation-patterns.txt");

        switch($option) {
            case "-w": {
                logtofile($path."/logs/patterns-for".$option.".txt", getPatternsForWord($target, $patternsList));
                echo hyphenate($target, getPatternsForWord($target, $patternsList));
                break;
            }
            case "-s": {
                $stringHyp = new Stringhyphenation($target, $patternsList);
                $stringHyp->result();
                break;
            }
            case "-f": {

                break;
            }
            default: {
                echo "\nSuch option ($option) not available.\n";
                break;
            }
        }
        $end_timing = microtime(true);
        echo "\nExecution time = ".($end_timing - $start_timing)." seconds\n";
    }
