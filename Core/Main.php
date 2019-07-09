<?php
declare(strict_types=1);

namespace Core;

use Algorithms\{Hyphenation, StringHyphenation};
use Core\Cache\FileCache;
use Core\Exceptions\ExceptionHandler;
use Validations\EmailValidation;
use Core\Scans\{Scan, ScanString};
use Core\Log\Logger;

class Main
{
    // Objects
    private $wordAlgorithm;
    private $stringAlgorithm;
    private $stringFromFile;
    private $config;
    private $emailValidator;
    private $loadTime;
    private $loggerConfig;
    private $logger = null;
    private $exceptionHandler;

    // Arguments [array] & Arguments count
    private $argv;
    private $argc;

    // Settings array from config file
    private $settings;

    // Cache settings
    private const DEFAULT_EXPIRATION = 86400;
    private const DIR_MODE = 0775;
    private const FILE_MODE = 0664;
    private $cache;

    public function __construct(array $argv, int $argc)
    {
        // Cache setup
        $cachePath = dirname(__FILE__, 2) . "/Output/Cache";
        $this->cache = new FileCache($cachePath, self::DEFAULT_EXPIRATION, self::DIR_MODE, self::FILE_MODE);

        assert(file_exists($cachePath));
        assert(is_writable($cachePath));

        // Application configuration
        $this->config = new Config("config");
        $this->loggerConfig = new Config("logger");

        $logConfig = $this->loggerConfig->getConfigSettings();

        if ($logConfig['LOG_ENABLED'])
            $this->logger = new Logger($this->loggerConfig);

        $this->loadTime = new LoadTime($this->logger);
        $this->exceptionHandler = new ExceptionHandler($this->logger);

        $this->settings = $this->config->getConfigSettings();

        $this->emailValidator = new EmailValidation($this->settings['EMAIL_VALIDATION_PATTERN']);

        $path = dirname(__FILE__, 2);

        $patterns = Scan::readDataFromFile($path . $this->settings['PATTERNS_SOURCE']);

        $this->wordAlgorithm = new Hyphenation($patterns, $this->logger);
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
        if ($this->cache->has($target)) {
            print($this->cache->get($target));
            return;
        }
        else {
            $result = "";
            switch ($option) {
                case "-w":
                    {
                        $result = $this->wordAlgorithm->hyphenate($target);
                        print($result);
                        break;
                    }
                case "-s":
                    {
                        $result = $this->stringAlgorithm->hyphenate($target);
                        print($result);
                        break;
                    }
                case "-f":
                    {
                        $this->stringFromFile->inputSrc($target);
                        $result = $this->stringFromFile->result();
                        print($result);
                        break;
                    }
                case "-email":
                    {
                        $result = $this->emailValidator->validate($target) === 1 ? "Email is valid." : "Email not valid.";
                        print($result);
                        break;
                    }
            }
            $this->cache->set($target, $result);
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

