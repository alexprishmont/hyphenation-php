<?php

namespace Core;

use \Algorithms\Hyphenation;
use \Algorithms\String\Stringhyphenation;
use \Validations\EmailValidation;

class Main
{
    private const patterns_file = "/tex-hyphenation-patterns.txt";

    private $word_algorithm;

    private $string_algorithm;

    public function __construct()
    {
        $path = dirname(__FILE__, 2);
        $patterns = Scan::get_data_from_file($path . self::patterns_file);

        $this->word_algorithm = new Hyphenation($patterns);
        $this->string_algorithm = new Stringhyphenation($this->word_algorithm);
    }

    public function startup(array $argv, int $argc): void
    {
        if ($argc > 3 || $argc <= 2) {
            echo "Your entered arguments are wrong!\n";
            echo "Usage:\n $argv[0] -w [word]\n $argv[0] -s [sentence]\n $argv[0] -f [file location]\n";
            echo "For email validation use: $argv[0] -email [you_email]\n";
        } else {
            if (isset($argv[1]) && isset($argv[2])) {
                $option = $argv[1];
                $target = $argv[2];

                $this->load_algorithm($option, $target);
            }
        }
    }

    public function load_algorithm(string $option, string $target): void
    {
        $start_timing = microtime(true);
        switch ($option) {
            case "-w":
                {
                    $algorithm = $this->word_algorithm;
                    print($algorithm->hyphenate($target));
                    break;
                }
            case "-s":
                {
                    $algorithm = $this->string_algorithm;
                    print($algorithm->hyphenate($target));
                    break;
                }
            case "-f":
                {
                    try {
                        $file = new \SplFileObject($target);
                        $string_for_hyphenation = "";

                        while (!$file->eof())
                            $string_for_hyphenation .= $file->fgets();

                        $algorithm = $this->string_algorithm;
                        print($algorithm->hyphenate($string_for_hyphenation));
                    } catch (\Exception $e) {
                        print("\n Error while openning file [$e] \n");
                    }
                    break;
                }
            case "-email":
                {
                    print(EmailValidation::validate($target) === 1 ? "Email is valid." : "Email not valid.");
                    break;
                }
        }
        $end_timing = microtime(true);
        print("\nScript execution time = " . ($end_timing - $start_timing) . " seconds\n");
    }
}

