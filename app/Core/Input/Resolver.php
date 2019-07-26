<?php
declare(strict_types=1);

namespace NXT\Core\Input;

use NXT\Application;
use NXT\Core\DI\Container;
use NXT\Core\DI\DependenciesLoader;
use NXT\Core\Exceptions\InvalidFlagException;
use NXT\Core\Input\Interfaces\ResolverInterface;

class Resolver implements ResolverInterface
{
    private static $objectName;

    private const ALLOWED_OBJECTS = [
        '-word' => 'proxy',
        '-sentence' => 'stringHyphenation',
        '-file' => 'fileHyphenation',
        '-reset' => 'cacheController',
        '-import' => 'import',
        '-migrate' => 'migration'
    ];

    public static function resolve(string $flag)
    {
        if (!Validator::validateFlag($flag)) {
            throw new InvalidFlagException('Such flag [' . $flag . '] does not exist.');
        }
        $objectName = self::getObjectName($flag);
        $object = self::getObject($objectName);
        self::$objectName = $objectName;
        return $object;
    }

    public static function callMethod(object $object, string $target)
    {
        if (self::isObjectAlgorithm()) {
            $result = 'Result: ' . $object->hyphenate($target);
            return $result;
        }

        $result = '';
        switch (self::$objectName) {
            case 'cacheController':
                $object->clear();
                $result = 'Cache successfully cleared.';
                break;
            case 'import':
                $scan = self::getObject('scan');
                $patterns = $scan->readDataFromFile(Application::$settings['PATTERNS_SOURCE']);
                $object
                    ->patterns($patterns)
                    ->importPatterns();
                $result = 'Patterns successfully imported from ' . Application::$settings['PATTERNS_SOURCE'];
                break;
            case 'migration':
                $object->migrate($target);
                $result = 'Migration successfully done.';
                break;
        }
        return $result;
    }

    private static function isObjectAlgorithm()
    {
        return (strpos(self::$objectName, 'Hyphenation') !== false) || (self::$objectName === 'proxy');
    }

    private static function getObject(string $objectName)
    {
        $container = new Container;
        return $container->get(
            DependenciesLoader::get()[$objectName]
        );
    }

    private static function getObjectName(string $method): string
    {
        foreach (self::ALLOWED_OBJECTS as $key => $value) {
            if ($key === $method) {
                return $value;
            }
        }
    }
}