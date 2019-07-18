<?php
declare(strict_types=1);

namespace Core;

use Core\Database\Connection;

class Controller
{
    protected $connection;

    public function __construct(Connection $db)
    {
        $this->connection = $db;
    }

}
