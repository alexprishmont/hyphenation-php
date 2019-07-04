<?php

namespace Core;
use \Algorithms\Hyphenation;
use \Algorithms\String\Stringhyphenation;

class Main {
    public static function load_algorithm($option, $target) {
        $start_timing = microtime(true);
        $path = dirname(__FILE__, 2);
        $patterns = Scan::get_data_from_file($path."/tex-hyphenation-patterns.txt");
        switch ($option) {
            case "-w": {
                $hyphenation = new Hyphenation($target, $patterns);
                print($hyphenation->hyphenate());
                break;
            }
            case "-s": {
                $stringhyphenation = new Stringhyphenation($target, $patterns);
                print($stringhyphenation->hyphenate());
                break;
            }
            default: {
                print("\nSuch option ($option) not available.\n");
                break;
            }
        }
        $end_timing = microtime(true);
        print("\nScript execution time: ".($end_timing - $start_timing)." seconds");
    }
}

