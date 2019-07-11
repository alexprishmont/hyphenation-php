<?php

namespace Core\Database;

use Algorithms\Hyphenation;
use Core\Database\Interfaces\DatabaseInterface;
use Core\Log\Logger;
use Core\Log\LogLevel;
use \PDO;

class Connection implements DatabaseInterface
{
    private $handle;
    private $dsn;
    private $options;

    private $logger;
    private $patterns;

    public function __construct(Logger $log, Hyphenation $hyph)
    {
        $this->dsn = "mysql:host=" . DatabaseSettings::get()['host'] .
            ";dbname=" . DatabaseSettings::get()['database'] .
            ";charset=" . DatabaseSettings::get()['charset'];

        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->handle = new PDO($this->dsn,
            DatabaseSettings::get()['user'],
            DatabaseSettings::get()['password'],
            $this->options);

        $this->logger = $log;
        $this->patterns = $hyph->patterns;
    }

    public function query(string $stmt, array $params = [])
    {
        $statement = $this->handle->prepare($stmt);
        $statement->execute($params);
        return $statement;
    }

    public function importPatterns()
    {

        

        /*
        $sql = "replace into patterns values select * from fn_split('{$statement}')";
        $this->handle->query($sql);
        */
    }
}