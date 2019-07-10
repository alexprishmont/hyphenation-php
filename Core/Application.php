<?php
declare(strict_types=1);

namespace Core;

use Core\DI\Container;
use Core\Exceptions\InvalidFlagException;
use Core\Log\LogLevel;
use Validations\EmailValidation;

class Application
{
    private $container;

    public static $settings;

    private $argv;
    private $argc;

    private const DEPENDENCIES = [
        'hyphenation' => 'Algorithms\Hyphenation',
        'stringHyphenation' => 'Algorithms\StringHyphenation',
        'fileHyphenation' => 'Core\Scans\ScanString',
        'exceptionhandler' => 'Core\Exceptions\ExceptionHandler',
        'cache' => 'Core\Cache\FileCache',
        'config' => 'Core\Config',
        'scan' => 'Core\Scans\Scan',
        'logger' => 'Core\Log\Logger',
        'cacheController' => 'Core\Cache\Cache',
    ];

    private const VALID_FLAGS = [
        "-w",
        "-s",
        "-f",
        "-email",
        "-reset"
    ];

    public function __construct(array $argv, int $argc)
    {
        LoadTime::startMeasuring();
        $this->container = new Container();

        $this->container
            ->set(self::DEPENDENCIES['config']);

        self::$settings = $this->container
            ->get(self::DEPENDENCIES['config'])
            ->get('config');

        $this->container
            ->set(self::DEPENDENCIES['cacheController']);

        @set_exception_handler([
            $this->container
                ->get(self::DEPENDENCIES['exceptionhandler']),
            'exceptionHandlerFunction'
        ]);

        $this->argv = $argv;
        $this->argc = $argc;

    }

    public function __destruct()
    {
        LoadTime::endMeasuring();
        if ($this->container
            ->get(self::DEPENDENCIES['logger'])
            ->getLoggerStatus()) {
            $this->container
                ->get(self::DEPENDENCIES['logger'])
                ->log(LogLevel::SUCCESS, "Script execution time {time} seconds.", ['time' => LoadTime::getTime()]);
        }
    }

    public function startup(): void
    {
        $this->validateInput();
    }

    private function validateInput()
    {
        $argv = $this->argv;
        $argc = $this->argc;

        if ($argc > 3 || $argc <= 2) {
            $this->printHelp();
            throw new InvalidFlagException("Invalid flags count.");
        } else {
            $this->validateFlag($argv[1]);
            $this->loadAlgorithm($argv);
        }
    }

    private function loadAlgorithm(array $argv): void
    {
        $option = $argv[1];
        $target = $argv[2];

        switch ($option) {
            case '-w':
                {
                    print($this->container
                        ->get(self::DEPENDENCIES['hyphenation'])
                        ->hyphenate($target)
                    );
                    break;
                }
            case '-s':
                {
                    print($this->container
                        ->get(self::DEPENDENCIES['stringHyphenation'])
                        ->hyphenate($target)
                    );
                    break;
                }
            case '-f':
                {
                    print($this->container
                        ->get(self::DEPENDENCIES['fileHyphenation'])
                        ->hyphenate($target)
                    );
                    break;
                }
            case '-email':
                {
                    print(EmailValidation::validate($target) === 1 ? "Email is valid." : "Email is not valid");
                    break;
                }
            case '-reset':
                {
                    if ($target == 'cache') {
                        $this->container
                            ->get(self::DEPENDENCIES['cacheController'])
                            ->clear();

                        if ($this->container
                            ->get(self::DEPENDENCIES['logger'])
                            ->getLoggerStatus()) {
                            $this->container
                                ->get(self::DEPENDENCIES['logger'])
                                ->log(LogLevel::SUCCESS, "Cache cleared.");
                        }
                    }
                    break;
                }
        }
        print(PHP_EOL);
    }

    private function printHelp(): void
    {
        print("\nUsage php {$this->argv[0]} [flag] [target]\n");
        foreach (self::VALID_FLAGS as $flag) {
            print("  php {$this->argv[0]} {$flag} [target]\n");
        }
        print(PHP_EOL);
    }

    private function validateFlag(string $flag)
    {
        $ok = false;
        foreach (self::VALID_FLAGS as $key) {
            if ($key == $flag)
                $ok = true;
        }

        if (!$ok) {
            $this->printHelp();
            throw new InvalidFlagException("Flag [{$flag}] does not exist.");
        }
    }
}