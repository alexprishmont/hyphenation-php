<?php

namespace Core;

use Core\Database\Singleton;

class Model
{
    protected $connectionHandle;

    public function __construct()
    {
        $this->connectionHandle = Singleton::getInstanceOf();
    }
}