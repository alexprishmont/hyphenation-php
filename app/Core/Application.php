<?php
declare(strict_types=1);

namespace Core;

use Core\DI\Container;
use Core\DI\DependenciesLoader;
use Core\Input\Resolver;
use Core\Input\Validator;
use Core\Log\LogLevel;

class Application
{
    private $container;

    private $logger;
    private $timing;

    public static $settings;

    public function __construct()
    {
        if (PHP_SAPI === 'cli') {
            $this->timing = new Timing;
            $this->timing->start();
        }

        $this->container = new Container;

        $this->setInstance('config');
        self::$settings = $this->getInstance('config')->get('config');

        $exceptionHandler = $this->getInstance('exceptionhandler');

        set_exception_handler([
            $exceptionHandler,
            'exceptionHandlerFunction'
        ]);

        $this->logger = $this->getInstance('logger');
    }

    public function __destruct()
    {
        $this->getInstance('config')
            ->set('config', self::$settings);

        if (PHP_SAPI === 'cli') {
            $this->timing->stop();

            $this->logger
                ->log(LogLevel::INFO,
                    'Script execution time {time} seconds.',
                    ['time' => $this->timing->printTiming()]);
            $this->logger
                ->log(LogLevel::INFO,
                    'Script used {memory} of memory.',
                    ['memory' => Memory::get()]);
        }
    }

    public function getInstance(string $instance): object
    {
        return $this->container
            ->get(DependenciesLoader::get()[$instance]);
    }

    public function setInstance(string $instance)
    {
        $this->container
            ->set(DependenciesLoader::get()[$instance]);
    }

    public function startup(array $input)
    {
        Validator::validateInput($input);

        $flag = $input[1];
        $target = $input[2];

        $object = Resolver::resolve($flag);
        $result = Resolver::callMethod($object, $target);

        $this->logger
            ->log(LogLevel::SUCCESS, $result);
    }
}