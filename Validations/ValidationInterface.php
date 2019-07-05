<?php

namespace Validations;

interface ValidationInterface
{
    public static function validate(string $data): int;
}
