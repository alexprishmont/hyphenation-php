<?php
/**
 * @param $src
 * @return array
 */
function getTextFileData($src) {
    $file = new SplFileObject($src);
    $data = [];
    foreach($file as $data_num => $data_c) {
        $data[] = $data_c;
    }
    return $data;
}

