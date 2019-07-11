<?php

namespace Core\Database;

use Core\Database\Interfaces\DatabaseInterface;
use \PDO;

class Connection implements DatabaseInterface
{
    private $handle;
    private $dsn;
    private $options;

    public function __construct()
    {
        $this->dsn = "mysql:host=" . DatabaseSettings::get()['host'] .
            ";dbname=" . DatabaseSettings::get()['database'] .
            ";charset=" . DatabaseSettings::get()['charset'];

        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

    }

    public function connect()
    {
        $this->handle = new PDO($this->dsn, DatabaseSettings::get()['user'], DatabaseSettings::get()['password'], $this->options);
    }

    public function query(string $stmt, array $params = [])
    {
        $statement = $this->handle->prepare($stmt);
        $statement->execute($params);
        return $statement;
    }
}