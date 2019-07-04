<?php

namespace Core;
use \Algorithms\Hyphenation;
use \Algorithms\String\Stringhyphenation;

class Main {
    public static function load_algorithm(string $option, string $target):void {
        $start_timing = microtime(true);
        $path = dirname(__FILE__, 2);
        $patterns = Scan::get_data_from_file($path."/tex-hyphenation-patterns.txt");
        switch ($option) {
            case "-w": {
                $hyphenation = new Hyphenation($patterns, $target);
                print($hyphenation->hyphenate());
                break;
            }
            case "-s": {
                $hyphenation = new Stringhyphenation($patterns, null, $target);
                print($hyphenation->hyphenate());
                break;
            }
            default: {
                print("\nSuch option ($option) not available.\n");
                break;
            }
        }
        $end_timing = microtime(true);
        print("\nScript execution time: ".($end_timing - $start_timing)." seconds\n");
    }
}

