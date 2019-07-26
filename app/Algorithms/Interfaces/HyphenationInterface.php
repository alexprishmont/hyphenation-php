<?php
declare(strict_types = 1);

namespace NXT\Algorithms\Interfaces;

interface HyphenationInterface
{
    public function hyphenate(string $word): string;
}
