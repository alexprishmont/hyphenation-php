<?php
declare(strict_types = 1);

namespace Core\Scans;

use SplFileObject;

class Scan
{
    public static function readDataFromFile(string $src): array
    {
        $file = new SplFileObject($src);
        $data = [];
        foreach ($file as $data_num => $data_c) {
            $data[] = $data_c;
        }
        return $data;
    }
}
