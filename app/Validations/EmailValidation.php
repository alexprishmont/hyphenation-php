<?php
declare(strict_types = 1);

namespace Validations;

use Validations\Interfaces\ValidationInterface;

class EmailValidation implements ValidationInterface
{

    public static function validate(string $email): int
    {
        return preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/', $email);
    }
}
