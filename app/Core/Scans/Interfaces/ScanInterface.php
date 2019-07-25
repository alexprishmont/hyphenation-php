<?php
declare(strict_types=1);

namespace Core\Scans\Interfaces;

interface ScanInterface
{
    public function readDataFromFile(string $src);
}