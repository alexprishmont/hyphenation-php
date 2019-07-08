<?php
declare(strict_types = 1);

namespace Core;

use Algorithms\{Hyphenation, StringHyphenation};
use Validations\EmailValidation;
use Core\Scans\{Scan, ScanString};

class Main
{
    // Objects
    private $wordAlgorithm;
    private $stringAlgorithm;
    private $stringFromFile;
    private $config;
    private $emailValidator;
    private $loadTime;

    // Arguments [array] & Arguments count
    private $argv;
    private $argc;

    // Settings array from config file
    private $settings;

    public function __construct(array $argv, int $argc)
    {
        $this->config = new Config();
        $this->loadTime = new LoadTime();

        $this->settings = $this->config->getConfigSettings();

        $this->emailValidator = new EmailValidation($this->settings['EMAIL_VALIDATION_PATTERN']);

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
                    print($this->emailValidator->validate($target) === 1 ? "Email is valid." : "Email not valid.");
                    break;
                }
        }
    }

    private function showAllowedFlags(): void
    {
        print("Your entered arguments are wrong!\n");
        print("Usage: 
            php " . $this->argv[0] . " -w [word] 
            php " . $this->argv[0] . " -s [sentence] 
            php " . $this->argv[0] . " -f [file location]"
        );
        print("\nFor validations use: 
            php " . $this->argv[0] . " -email [you_email]\n"
        );
    }
}

