<?php

namespace Core;

use Core\Database\Connection;

class Model
{
    protected $connectionHandle;

    public function __construct(Connection $db)
    {
        $this->connectionHandle = $db;
    }
}