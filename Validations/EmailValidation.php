<?php
declare(strict_types = 1);

namespace Validations;

use Validations\Interfaces\ValidationInterface;

class EmailValidation implements ValidationInterface
{
    private $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function validate(string $email): int
    {
        return preg_match($this->pattern, $email);
    }
}
