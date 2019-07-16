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

    private $mysql;
    private $cacheController;
    private $exceptionHandler;
    private $hyphenation;
    private $stringHyphenation;
    private $fileHyphenation;
    private $logger;

    private $argv;
    private $argc;

    public static $settings;

    private const VALID_FLAGS = [
        "-word" => '[word]',
        "-sentence" => '["sentence"]',
        "-file" => '["path to file"]',
        "-email" => '[email]',
        "-reset" => 'cache',
        "-import" => 'patterns',
        "-source" => 'file/database',
        "-migrate" => '[migration file name]',
    ];

    const FILE_SOURCE = 'file';
    const DB_SOURCE = 'database';

    public function __construct(array $argv, int $argc)
    {
        $this->container = new Container();
        LoadTime::startMeasuring();

        $this->setInstance("config");
        self::$settings = $this->getInstance("config")
            ->get("config");

        $this->exceptionHandler = $this->getInstance("exceptionhandler");

        @set_exception_handler([
            $this->exceptionHandler,
            'exceptionHandlerFunction'
        ]);

        $this->setInstance("mysql");
        $this->setInstance("cacheController");

        $this->cacheController = $this->getInstance("cacheController");
        $this->mysql = $this->getInstance("mysql");
        $this->hyphenation = $this->getInstance("hyphenation");
        $this->stringHyphenation = $this->getInstance("stringHyphenation");
        $this->fileHyphenation = $this->getInstance("fileHyphenation");
        $this->logger = $this->getInstance("logger");

        $this->argv = $argv;
        $this->argc = $argc;
    }

    public function __destruct()
    {
        $this->getInstance('config')
            ->write('DEFAULT_SOURCE',
                self::$settings['DEFAULT_SOURCE'],
                'config');

        LoadTime::endMeasuring();

        $this->logger
            ->log(LogLevel::INFO,
                "Script execution time {time} seconds.",
                ['time' => LoadTime::getTime()]);
        $this->logger
            ->log(LogLevel::INFO,
                "Script used {memory} of memory.",
                ['memory' => Memory::get()]);
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
            case '-word':
                print($this->hyphenation->hyphenate($target) . PHP_EOL);
                break;
            case '-sentence':
                print($this->stringHyphenation->hyphenate($target) . PHP_EOL);
                break;
            case '-file':
                print($this->fileHyphenation->hyphenate($target) . PHP_EOL);
                break;
            case '-reset':
                if ($target == 'cache') {
                    $this->resetCache();
                }
                break;
            case '-import':
                $this->importFiles();
                break;
            case '-source':
                $this->changeSource($target);
                break;
            case '-migrate':
                $this->getInstance('migration')->migrate($target);
                break;
        }
    }

    private function changeSource($source): void
    {
        if ($source == self::DB_SOURCE || $source == self::FILE_SOURCE) {
            self::$settings['DEFAULT_SOURCE'] = $source;
            $this->logger
                ->log(LogLevel::SUCCESS,
                    "You changed script's source to '{target}'",
                    ['target' => $source]);
        } else {
            throw new InvalidFlagException("Your entered new source[{$source}] is invalid.");
        }
    }

    private function resetCache(): void
    {
        $this->cacheController->clear();
        $this->logger->log(LogLevel::SUCCESS, "Cache cleared.");
    }

    private function importFiles(): void
    {
        $source = $this->argv[2];
        if ($source == 'patterns') {
            $this->logger
                ->log(LogLevel::NOTICE,
                    "Patterns would be loaded from {src}!",
                    ['src' => self::$settings['PATTERNS_SOURCE']]);

            $this->mysql->importPatterns();

        } else {
            $this->logger
                ->log(LogLevel::ERROR,
                    "Such source [{source}] not available.",
                    ['source' => $source]);
        }
    }

    private function validateArguments(): void
    {
        $arguments = $this->argc;

        if ($arguments <= 2 || $arguments > 3) {
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