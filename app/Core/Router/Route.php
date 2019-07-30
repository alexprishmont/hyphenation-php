<?php
declare(strict_types=1);

namespace NXT\Core\Router;

use NXT\Core\Router\Interfaces\RouterInterface;

class Route implements RouterInterface
{
    private static $routes = [];
    private static $pathNotFound = null;
    private static $methodNotAllowed = null;

    public static function add(
        $expression,
        $function,
        string $method = 'get'
    ): void {
        self::$routes[] = [
            'expression' => $expression,
            'function' => $function,
            'method' => $method
        ];
    }

    public static function pathNotFound($function): void
    {
        self::$pathNotFound = $function;
    }

    public static function methodNotAllowed($function): void
    {
        self::$methodNotAllowed = $function;
    }

    public static function run(string $basepath = '/'): void
    {
        $path = self::getPath();
        $method = self::getMethod();

        $pathMatchFound = false;
        $routeMatchFound = false;

        foreach (self::$routes as $route) {
            if ($basepath !== '' && $basepath !== '/') {
                $route['expression'] = '(' . $basepath . ')' . $route['expression'];
            }

            $route['expression'] = '^' . $route['expression'];
            $route['expression'] = $route['expression'] . '$';
            if (preg_match('#' . $route['expression'] . '#', $path, $matches)) {
                $pathMatchFound = true;
                if (strtolower($method) === strtolower($route['method'])) {
                    array_shift($matches);
                    if ($basepath !== '' && $basepath !== '/') {
                        array_shift($matches);
                    }
                    call_user_func_array($route['function'], $matches);
                    $routeMatchFound = true;
                    break;
                }
            }
        }

        self::noRouteMatchFoundResponse($routeMatchFound, $pathMatchFound, $path, $method);
    }

    private static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private static function getPath(): string
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
        if (!isset($parsedUrl['path'])) {
            return '/';
        }
        return $parsedUrl['path'];
    }

    private static function noRouteMatchFoundResponse(
        bool $routeMatchFound,
        bool $pathMatchFound,
        string $path,
        string $method
    ): void {
        if (!$routeMatchFound) {
            if ($pathMatchFound) {
                header("HTTP/1.1 405 Method Not Allowed");
                if (self::$methodNotAllowed) {
                    call_user_func_array(self::$methodNotAllowed, [$path, $method]);
                }
            } else {
                header("HTTP/1.1 404 Not Found");
                if (self::$pathNotFound) {
                    call_user_func_array(self::$pathNotFound, [$path]);
                }
            }
        }
    }
}