<?php

namespace Core;

use Algorithms\{Hyphenation, StringHyphenation};
use Validations\EmailValidation;

class Main
{
    private $wordAlgorithm;
    private $stringAlgorithm;
    private $stringFromFile;
    private $config;

    private $argv;
    private $argc;

    private $settings;

    public function __construct(array $argv, int $argc)
    {
        $this->config = new Config();
        $this->settings = $this->config->getConfigSettings();

        $path = dirname(__FILE__, 2);
        $patterns = Scan::readDataFromFile($path . $this->settings['PATTERNS_SOURCE']);

        $this->wordAlgorithm = new Hyphenation($patterns);
        $this->stringAlgorithm = new StringHyphenation($this->wordAlgorithm);
        $this->stringFromFile = new ScanString($this->stringAlgorithm);

        $this->argv = $argv;
        $this->argc = $argc;
    }

    public function startup(): void
    {
        $argv = $this->argv;
        $argc = $this->argc;

        if ($argc > 3 || $argc <= 2)
            $this->showAllowedFlags();
        else {
            if (isset($argv[1]) && isset($argv[2])) {
                $option = $argv[1];
                $target = $argv[2];
                $this->loadAlgorithm($option, $target);
            }
        }
    }

    public function loadAlgorithm(string $option, string $target): void
    {
        $start_timing = microtime(true);
        switch ($option) {
            case "-w":
                {
                    print($this->wordAlgorithm->hyphenate($target));
                    break;
                }
            case "-s":
                {
                    print($this->stringAlgorithm->hyphenate($target));
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

    private function showAllowedFlags(): void
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

