<?php

namespace Validations;

use Validations\Interfaces\ValidationInterface;

class EmailValidation implements ValidationInterface
{
    private $pattern;

    public function __construct()
    {
        $this->pattern = "/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/";
    }

    public function validate(string $email): int
    {
        return preg_match($this->pattern, $email);
    }
}
