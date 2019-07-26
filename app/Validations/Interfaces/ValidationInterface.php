<?php
declare(strict_types = 1);

namespace NXT\Validations\Interfaces;

interface ValidationInterface
{
    public static function validate(string $data): int;
}
