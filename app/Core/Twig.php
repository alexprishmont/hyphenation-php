<?php
declare(strict_types=1);
namespace NXT\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Twig
{
    private static $instance;
    private $twig;

    public function __construct()
    {
        $viewsPath = dirname(__FILE__, 3) . '/public/views';
        $loader = new FilesystemLoader($viewsPath);
        $this->twig = new Environment($loader);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Twig();
        }
        return self::$instance;
    }

    public function twig()
    {
        return $this->twig;
    }
}