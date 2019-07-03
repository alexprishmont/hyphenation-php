<?php
require_once("core/main.php");

if ($argc > 3 || $argc < 2) {
    echo "Your entered arguments are wrong!\n";
    echo "Usage:\n $argv[0] -w [word]\n $argv[0] -s [sentence]\n $argv[0] -f [file location]\n";
}
else {
    if (isset($argv[1]) && isset($argv[2])) {
        $option = $argv[1];
        $target = $argv[2];
        makeMagic($option, $target);
    }
}