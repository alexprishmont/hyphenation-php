<?php
declare(strict_types=1);

namespace Core;

use Core\DI\Container;
use Core\DI\DependenciesLoader;
use Core\Exceptions\InvalidFlagException;
use Core\Log\LogLevel;
use Validations\EmailValidation;

class Application
{
    private $container;

    public static $settings;

    private $argv;
    private $argc;

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

        @set_exception_handler([
            $this->container
                ->get(DependenciesLoader::get()['exceptionhandler']),
            'exceptionHandlerFunction'
        ]);

        $this->container
            ->set(DependenciesLoader::get()['config']);

        self::$settings = $this->container
            ->get(DependenciesLoader::get()['config'])
            ->get('config');

        $this->container
            ->set(DependenciesLoader::get()['cacheController']);

        $this->argv = $argv;
        $this->argc = $argc;

    }

    public function __destruct()
    {
        LoadTime::endMeasuring();
        if ($this->container
            ->get(DependenciesLoader::get()['logger'])
            ->getLoggerStatus()) {
            $this->container
                ->get(DependenciesLoader::get()['logger'])
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
                        ->get(DependenciesLoader::get()['hyphenation'])
                        ->hyphenate($target)
                    );
                    break;
                }
            case '-s':
                {
                    print($this->container
                        ->get(DependenciesLoader::get()['stringHyphenation'])
                        ->hyphenate($target)
                    );
                    break;
                }
            case '-f':
                {
                    print($this->container
                        ->get(DependenciesLoader::get()['fileHyphenation'])
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
                            ->get(DependenciesLoader::get()['cacheController'])
                            ->clear();

                        if ($this->container
                            ->get(DependenciesLoader::get()['logger'])
                            ->getLoggerStatus()) {
                            $this->container
                                ->get(DependenciesLoader::get()['logger'])
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