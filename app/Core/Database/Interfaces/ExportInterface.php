<?php
declare(strict_types=1);

namespace NXT\Core\Database\Interfaces;


interface ExportInterface
{
    public function extractPatternsFromDatabase();
}