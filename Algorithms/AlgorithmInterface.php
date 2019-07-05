<?php

namespace Algorithms;

interface AlgorithmInterface
{
    public function __construct(array $patterns);

    public function hyphenate(string $word): string;
}