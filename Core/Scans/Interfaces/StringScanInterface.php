<?php
declare(strict_types=1);

namespace Core\Scans\Interfaces;


interface StringScanInterface
{
    public function hyphenate(string $src): string;

}