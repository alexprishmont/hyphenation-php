<?php

namespace Validations\Interfaces;

interface ValidationInterface
{
    public function validate(string $data): int;
}
