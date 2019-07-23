<?php
declare(strict_types=1);

namespace Core\Database;

use Core\Database\Interfaces\DatabaseInterface;
use PDO;

class Singleton implements DatabaseInterface
{
    private $handle;
    private static $instance = null;

    public function __construct()
    {
        $dsn = "mysql:host=" . DatabaseSettings::get()['host'] .
            ";dbname=" . DatabaseSettings::get()['database'] .
            ";charset=" . DatabaseSettings::get()['charset'];

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->handle = new PDO($dsn,
            DatabaseSettings::get()['user'],
            DatabaseSettings::get()['password'],
            $options);
    }

    public static function getInstanceOf()
    {
        if (!self::$instance) {
            self::$instance = new Singleton();
        }
        return self::$instance;
    }

    public function getHandle()
    {
        return $this->handle;
    }
}