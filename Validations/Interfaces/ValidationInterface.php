<?php

namespace Validations\Interfaces;

interface ValidationInterface
{
    public static function validate(string $data): int;
}
