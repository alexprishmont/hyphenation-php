<?php

namespace NXT\Core\Database\Interfaces;

interface MigrationInterface
{
    public function migrate(string $name): void;
}