<?php
declare(strict_types = 1);

namespace Validations\Interfaces;

interface ValidationInterface
{
    public function validate(string $data): int;
}
