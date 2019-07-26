<?php
declare(strict_types=1);

namespace NXT\Core\Input\Interfaces;


interface ResolverInterface
{
    public static function resolve(string $flag);
}