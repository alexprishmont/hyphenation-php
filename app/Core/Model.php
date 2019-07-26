<?php

namespace NXT\Core;

use NXT\Core\Database\QueryBuilder;
use NXT\Core\Database\Singleton;

class Model
{
    protected $connectionHandle;
    protected $builder;

    public function __construct()
    {
        $this->builder = QueryBuilder::getInstanceOf();
        $this->connectionHandle = Singleton::getInstanceOf();
    }

    public function count()
    {
    }

    public function find()
    {
    }

    public function read()
    {
    }

    public function create()
    {
    }

    public function delete()
    {
    }

}