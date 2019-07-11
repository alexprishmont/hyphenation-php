<?php
declare(strict_types=1);

namespace Core;

use Core\DI\Container;
use Core\DI\DependenciesLoader;
use Core\Exceptions\InvalidFlagException;
use Core\Log\LogLevel;

class Application
{
    private $container;

    private $argv;
    private $argc;

    public static $settings;

    private const VALID_FLAGS = [
        "-w",
        "-s",
        "-f",
        "-email",
        "-reset",
        "-database",
        "-import"
    ];

    public function __construct(array $argv, int $argc)
    {
        $this->container = new Container();
        LoadTime::startMeasuring();

        @set_exception_handler([
            $this->getInstance("exceptionhandler"),
            'exceptionHandlerFunction'
        ]);

        $this->setInstance("mysql");

        $this->setInstance("config");
        self::$settings = $this->getInstance("config")
            ->get("config");

        $this->setInstance("cacheController");

        $this->argv = $argv;
        $this->argc = $argc;
    }

    public function __destruct()
    {
        LoadTime::endMeasuring();
        $this->getInstance('logger')
            ->log(LogLevel::WARNING, "Script execution time {time} seconds.", ['time' => LoadTime::getTime()]);
        $this->getInstance('logger')
            ->log(LogLevel::WARNING, "Script used {memory} of memory.", ['memory' => Memory::get()]);
    }

    public function startup(): void
    {
        $this->validateArguments();
        $this->validateFlag($this->argv[1]);

        if ($this->argv[1] == '-reset') {
            if ($this->argv[2] == 'cache') {
                $this->getInstance('cacheController')
                    ->clear();

                $this->getInstance('logger')->log(LogLevel::SUCCESS, "Cache cleared.");
            }
        } else if ($this->argv[1] == '-import') {
            $source = readline("\n Source [patterns/words]? ");
            if ($source == 'words') {
                $src = readline("\n Please enter source path to the words file: ");

            } else if ($source == 'patterns') {
                $this->getInstance('logger')
                    ->log(LogLevel::WARNING,
                        "Patterns would be loaded from {src}!",
                        ['src' => self::$settings['PATTERNS_SOURCE']]);

                $this->getInstance('mysql')
                    ->importPatterns(self::$settings['PATTERNS_SOURCE']);

            } else {
                $this->getInstance('logger')
                    ->log(LogLevel::ERROR, "Such source [{source}] not available.", ['source' => $source]);
            }
        }

    }

    private function validateArguments(): void
    {
        $arguments = $this->argc;

        if (($arguments <= 2 && $this->argv[1] != '-import') || $arguments > 3) {
            $this->printHelp();
            throw new InvalidFlagException("Your entered arguments count is not valid.");
        }
    }

    private function validateFlag(string $flag): void
    {
        $ok = false;

        foreach (self::VALID_FLAGS as $validFlag) {
            if ($flag == $validFlag) {
                $ok = true;
                break;
            }
        }

        if (!$ok) {
            $this->printHelp();
            throw new InvalidFlagException("Your entered flag does not exist.");
        }
    }

    private function printHelp(): void
    {
        print("\nUsage: php {$this->argv[0]} [flag] [target]\n");
        foreach (self::VALID_FLAGS as $flag) {
            print("  php {$this->argv[0]} {$flag} [target]\n");
        }
        print(PHP_EOL);
    }

    private function getInstance(string $instance): object
    {
        return $this->container
            ->get(DependenciesLoader::get()[$instance]);
    }

    private function setInstance(string $instance)
    {
        $this->container
            ->set(DependenciesLoader::get()[$instance]);
    }

}