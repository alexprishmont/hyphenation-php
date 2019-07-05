<?php

namespace Core;

use Algorithms\Hyphenation;
use Algorithms\String\Stringhyphenation;
use Validations\EmailValidation;

class Main
{
    private const patterns_file = "/tex-hyphenation-patterns.txt";

    private $word_algorithm;
    private $string_algorithm;
    private $stringFromFile;

    private $argv;
    private $argc;

    public function __construct(array $argv, int $argc)
    {
        $path = dirname(__FILE__, 2);
        $patterns = Scan::get_data_from_file($path . self::patterns_file);

        $this->word_algorithm = new Hyphenation($patterns);
        $this->string_algorithm = new Stringhyphenation($this->word_algorithm);
        $this->stringFromFile = new ScanString($this->string_algorithm);

        $this->argv = $argv;
        $this->argc = $argc;
    }

    public function startup(): void
    {
        $argv = $this->argv;
        $argc = $this->argc;

        if ($argc > 3 || $argc <= 2)
            $this->show_choice();
        else {
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
                    print($this->word_algorithm->hyphenate($target));
                    break;
                }
            case "-s":
                {
                    print($this->string_algorithm->hyphenate($target));
                    break;
                }
            case "-f":
                {
                    $this->stringFromFile->inputSrc($target);
                    print($this->stringFromFile->result());
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

    private function show_choice(): void
    {
        print("Your entered arguments are wrong!\n");
        print("Usage: 
            php " . $this->argv[0] . " -w [word] 
            php " . $this->argv[0] . " -s [sentence] 
            php " . $this->argv[0] . " -f [file location]"
        );
        print("\nFor email validation use: 
            php " . $this->argv[0] . " -email [you_email]\n"
        );
    }
}

