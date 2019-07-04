<?php

namespace Core;
use \Algorithms\Hyphenation;
use \Algorithms\String\Stringhyphenation;
use \Validations\EmailValidation;

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
            case "-f": {
                try {
                    $file = new \SplFileObject($target);
                    $string_for_hyphenation = "";

                    while (!$file->eof())
                        $string_for_hyphenation .= $file->fgets();

                    $hyphenation = new Stringhyphenation($patterns, null, $string_for_hyphenation);
                    print($hyphenation->hyphenate());
                }
                catch (\Exception $e) {
                    print("\n Error $e\n");
                }
                break;
            }
            case "-email": {
                $validation = new EmailValidation($target);
                print(($validation->validate()) === 1 ? "This email is valid." : "Email not valid.");
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

