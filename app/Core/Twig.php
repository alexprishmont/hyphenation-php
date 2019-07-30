<?php
declare(strict_types=1);
namespace NXT\Core;

use NXT\Application;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Twig
{
    private static $instance;
    private $twig;
    private $templates;

    public function __construct()
    {
        $viewsPath = dirname(__FILE__, 3) . '/public/views';
        $loader = new FilesystemLoader($viewsPath);

        $this->twig = new Environment($loader,
            [
                'cache' => Tools::getDefaultCachePath(Application::$settings)
            ]
        );

        $this->preloadTemplates($viewsPath);
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

    public function getTemplates()
    {
        return $this->templates;
    }

    private function preloadTemplates($path)
    {
        $templates = scandir($path, 1);
        foreach ($templates as $template) {
            if (strpos($template, '.twig') === false) {
                continue;
            }
            $key = substr($template, 0, strpos($template, '.'));
            $this->templates[$key] = $this->twig->load($template);
        }
    }
}