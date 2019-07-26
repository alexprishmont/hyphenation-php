<?php

namespace NXT\Core\Database\Interfaces;

interface DatabaseInterface
{
    public static function getInstanceOf();

    public function getHandle();
}