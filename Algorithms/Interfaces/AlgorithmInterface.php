<?php
declare(strict_types = 1);

namespace Algorithms\Interfaces;

interface AlgorithmInterface
{
    public function hyphenate(string $word): string;
}