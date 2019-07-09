<?php
declare(strict_types=1);

namespace Core;

use Core\DI\Container;

class Application
{
    private $container;

    private $argv;
    private $argc;

    private const DEPENDENCIES = [
        'hyphenation' => 'Algorithms\Hyphenation',
        'stringHyphenation' => 'Algorithms\StringHyphenation',
        'loadtime' => 'Core\LoadTime'
    ];

    public function __construct(array $argv, int $argc)
    {
        $this->container = new Container();

        $this->container->set(self::DEPENDENCIES['hyphenation']);
        $this->container->set(self::DEPENDENCIES['stringHyphenation']);


        $this->argv = $argv;
        $this->argc = $argc;

        $this->container->get(self::DEPENDENCIES['loadtime']);
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
            // TO-DO show help'out flags
        } else {
            // TO-DO flags validation, if they're valid
        }
    }
}