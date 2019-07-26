<?php
declare(strict_types=1);

namespace NXT\Core\Input\Interfaces;

interface ValidatorInterface
{
    public static function validateFlag(string $input): bool;

    public static function validateInput(array $input);
}