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

    private $exceptionHandler;
    private $logger;
    private $timing;

    private $input;

    public static $settings;

    public function __construct(array $input)
    {
        if (PHP_SAPI === 'cli') {
            $this->timing = new Timing;
            $this->timing->start();
        }

        $this->container = new Container();

        $this->setInstance('config');
        self::$settings = $this->getInstance('config')->get('config');

        $this->exceptionHandler = $this->getInstance('exceptionhandler');

        set_exception_handler([
            $this->exceptionHandler,
            'exceptionHandlerFunction'
        ]);

        $this->logger = $this->getInstance('logger');

        $this->input = $input;
    }

    public function __destruct()
    {
        $this->getInstance('config')
            ->write('DEFAULT_SOURCE',
                self::$settings['DEFAULT_SOURCE'],
                'config');

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

    public function startup()
    {
        Validator::validateInput($this->input);
        $object = Resolver::resolve($this->input[1]);
        $target = $this->input[2];
        $result = Resolver::callMethod($object, $target);
        $this->logger
            ->log(LogLevel::SUCCESS, $result);
    }
}