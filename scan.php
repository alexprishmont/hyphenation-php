<?php

    function getUserInput() {
        $handle = fopen("php://stdin", "r");
        return trim(fgets($handle));
    }

    function getTextFileData($src) {
        $file = new SplFileObject($src);
        $data = [];
        foreach($file as $data_num => $data_c) {
            $data[] = $data_c;
        }
        return $data;
    }

