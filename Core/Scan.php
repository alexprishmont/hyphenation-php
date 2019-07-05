<?php

namespace Core;

class Scan
{
    public static function get_data_from_file(string $src): array
    {
        $file = new \SplFileObject($src);
        $data = [];
        foreach ($file as $data_num => $data_c) {
            $data[] = $data_c;
        }
        return $data;
    }
}
