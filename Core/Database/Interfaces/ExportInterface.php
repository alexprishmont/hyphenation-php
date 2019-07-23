<?php
declare(strict_types=1);

namespace Core\Database\Interfaces;


interface ExportInterface
{
    public function extractPatternsFromDatabase();
}