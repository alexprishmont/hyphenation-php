<?php
declare(strict_types=1);

namespace Core;

use Core\DI\Container;
use Core\Exceptions\InvalidFlagException;
use Core\Log\LogLevel;

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
        //'emailValidator' => 'Validations\EmailValidation'
    ];

    private const VALID_FLAGS = [
        "-w",
        "-s",
        "-f",
        "-email",
        "-reset-cache"
    ];

    public function __construct(array $argv, int $argc)
    {
        LoadTime::startMeasuring();
        $this->container = new Container();

        $this->container->set(self::DEPENDENCIES['config']);
        self::$settings = $this->container->get(self::DEPENDENCIES['config'])->get('config');

        $this->argv = $argv;
        $this->argc = $argc;

    }

    public function __destruct()
    {
        LoadTime::endMeasuring();
        if ($this->container->get(self::DEPENDENCIES['logger'])->getLoggerStatus()) {
            $this->container->get(self::DEPENDENCIES['logger'])->log(LogLevel::SUCCESS, "Script execution time {time} seconds.", ['time' => LoadTime::getTime()]);
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
            print("\nInvalid flags count.\n");
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
                    print($this->container->get(self::DEPENDENCIES['hyphenation'])->hyphenate($target));
                    break;
                }
            case '-s':
                {
                    print($this->container->get(self::DEPENDENCIES['stringHyphenation'])->hyphenate($target));
                    break;
                }
            case '-f':
                {
                    $this->container->get(self::DEPENDENCIES['fileHyphenation'])->inputSrc($target);
                    print($this->container->get(self::DEPENDENCIES['fileHyphenation'])->hyphenate());
                    break;
                }
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

        if (!$ok)
            throw new InvalidFlagException("Flag [{$flag}] does not exist.");
    }
}