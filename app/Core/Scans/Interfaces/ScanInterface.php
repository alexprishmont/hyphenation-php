<?php
declare(strict_types=1);

namespace NXT\Core\Scans\Interfaces;

interface ScanInterface
{
    public function readDataFromFile(string $src);
}