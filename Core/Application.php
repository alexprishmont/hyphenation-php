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

    private $argv;
    private $argc;

    public static $settings;

    private const VALID_FLAGS = [
        "-w" => '[word]',
        "-s" => '["sentence"]',
        "-f" => '["path to file"]',
        "-email" => '[email]',
        "-reset" => 'cache',
        "-import" => 'patterns/words',
        "-source" => 'file/database',
        "-migrate" => '[migration file name]',
    ];

    const FILE_SOURCE = 'file';
    const DB_SOURCE = 'database';

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
        $this->getInstance('config')->write('DEFAULT_SOURCE', self::$settings['DEFAULT_SOURCE'], 'config');

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
        $this->loadAlgorithm();
    }

    private function loadAlgorithm(): void
    {
        $target = $this->argv[2];
        switch ($this->argv[1]) {
            case '-w':
                {
                    print($this->getInstance('hyphenation')->hyphenate($target) . PHP_EOL);
                    break;
                }
            case '-s':
                {
                    print($this->getInstance('stringHyphenation')->hyphenate($target) . PHP_EOL);
                    break;
                }
            case '-f':
                {
                    print($this->getInstance('fileHyphenation')->hyphenate($target) . PHP_EOL);
                    break;
                }
            case '-email':
                {
                    print(EmailValidation::validate($target) ? "Email is valid." : "Email is not valid.");
                    break;
                }
            case '-reset':
                {
                    if ($target == 'cache') {
                        $this->resetCache();
                    }
                    break;
                }
            case '-import':
                {
                    $this->importFiles();
                    break;
                }
            case '-source':
                {
                    if ($target == self::DB_SOURCE || $target == self::FILE_SOURCE) {
                        self::$settings['DEFAULT_SOURCE'] = $target;
                    } else {
                        throw new InvalidFlagException("Your entered new source[{$target}] is invalid.");
                    }
                    break;
                }
            case '-migrate':
                {
                    $this->getInstance('migration')->migrate($target);
                    break;
                }
        }
    }

    private function resetCache(): void
    {
        $this->getInstance('cacheController')
            ->clear();
        $this->getInstance('logger')->log(LogLevel::SUCCESS, "Cache cleared.");
    }

    private function importFiles(): void
    {
        $source = $this->argv[2];
        if ($source == 'words') {
            $src = readline("\n Please enter source path to the words file: ");
            $this->getInstance('mysql')->importWords($src);

        } else if ($source == 'patterns') {
            $this->getInstance('logger')
                ->log(LogLevel::WARNING,
                    "Patterns would be loaded from {src}!",
                    ['src' => self::$settings['PATTERNS_SOURCE']]);

            $this->getInstance('mysql')
                ->importPatterns();

        } else {
            $this->getInstance('logger')
                ->log(LogLevel::ERROR, "Such source [{source}] not available.", ['source' => $source]);
        }
    }

    private function validateArguments(): void
    {
        $arguments = $this->argc;

        if ($arguments <= 2 || ($arguments > 3 && $this->argv[1] != '-import')) {
            $this->printHelp();
            throw new InvalidFlagException("Your entered arguments count is not valid.");
        }
    }

    private function validateFlag(string $flag): void
    {
        $ok = false;

        foreach (self::VALID_FLAGS as $key => $value) {
            if ($flag == $key) {
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
        foreach (self::VALID_FLAGS as $key => $value) {
            print("  php {$this->argv[0]} {$key} {$value}\n");
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