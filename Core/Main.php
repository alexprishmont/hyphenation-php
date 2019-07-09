<?php
declare(strict_types=1);

namespace Core;

use Algorithms\{Hyphenation, StringHyphenation};
use Core\Cache\FileCache;
use Core\Exceptions\ExceptionHandler;
use Core\Log\LogLevel;
use Validations\EmailValidation;
use Core\Scans\{Scan, ScanString};
use Core\Log\Logger;

class Main
{
    // Objects
    private $container = [];

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
        $this->loadDependencies();
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
                    $result = $this->container['word_algorithm']->hyphenate($target);
                    print($result);
                    break;
                }
            case "-s":
                {
                    $result = $this->container['string_algorithm']->hyphenate($target);
                    print($result);
                    break;
                }
            case "-f":
                {
                    $this->container['scan_string_service']->inputSrc($target);
                    $result = $this->container['scan_string_service']->result();
                    print($result);
                    break;
                }
            case "-email":
                {
                    $result = $this->container['email_validator']->validate($target) === 1 ? "Email is valid." : "Email not valid.";
                    print($result);
                    break;
                }
            default:
                {
                    $this->showAllowedFlags();
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

    private function getDefaultCachePath(): string
    {
        $cachePath = dirname(__FILE__, 2) . "/Output/Cache";
        assert(file_exists($cachePath));
        assert(is_writable($cachePath));
        return $cachePath;
    }

    private function getDefaultPatternList(): array
    {
        if (!$this->cache->has("patterns")) {
            $path = dirname(__FILE__, 2);
            $patterns = Scan::readDataFromFile($path . $this->settings['PATTERNS_SOURCE']);
            $this->cache->set("patterns", $patterns);
            return $patterns;
        } else {
            return $this->cache->get("patterns");
        }
    }

    private function loadDependencies(): void
    {
        $this->cache = new FileCache($this->getDefaultCachePath(), self::DEFAULT_EXPIRATION, self::DIR_MODE, self::FILE_MODE);

        $this->container['config'] = new Config("config");
        $this->settings = $this->container['config']->getConfigSettings();

        $this->container['logger_config'] = new Config("logger");
        $logConfig = $this->container['logger_config']->getConfigSettings();
        if ($logConfig['LOG_ENABLED'])
            $this->container['logger'] = new Logger($this->container['logger_config']);
        else
            $this->container['logger'] = null;

        $this->container['load_time'] = new LoadTime($this->container['logger']);
        $this->container['exception_handler'] = new ExceptionHandler($this->container['logger']);

        $logConfig = $this->container['logger_config']->getConfigSettings();

        if ($logConfig['LOG_ENABLED'])
            $this->container['logger'] = new Logger($this->container['logger_config']);


        $this->container['email_validator'] = new EmailValidation($this->settings['EMAIL_VALIDATION_PATTERN']);
        $this->container['word_algorithm'] = new Hyphenation($this->getDefaultPatternList(), $this->cache, $this->container['logger']);
        $this->container['string_algorithm'] = new StringHyphenation($this->container['word_algorithm'], $this->cache);
        $this->container['scan_string_service'] = new ScanString($this->container['string_algorithm'], $this->cache);
    }
}

