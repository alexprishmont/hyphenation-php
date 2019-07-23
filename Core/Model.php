<?php

namespace Core;

use Core\Database\QueryBuilder;
use Core\Database\Singleton;

class Model
{
    protected $connectionHandle;
    protected $builder;

    public function __construct()
    {
        $this->builder = QueryBuilder::getInstanceOf();
        $this->connectionHandle = Singleton::getInstanceOf();
    }
}