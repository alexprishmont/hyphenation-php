<?php

use Core\Main;

require_once("Core/Autoloader.php");
Core\Autoloader::register();

$app = new Main();

if ($argc > 3 || $argc <= 2) {
    echo "Your entered arguments are wrong!\n";
    echo "Usage:\n $argv[0] -w [word]\n $argv[0] -s [sentence]\n $argv[0] -f [file location]\n";
    echo "For email validation use: $argv[0] -email [you_email]\n";
} else {
    if (isset($argv[1]) && isset($argv[2])) {
        $option = $argv[1];
        $target = $argv[2];

        $app->load_algorithm($option, $target);
    }
}