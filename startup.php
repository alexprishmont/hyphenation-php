<?php
require_once("Core/Autoloader.php");

Core\Autoloader::register();

$app = new Core\Main();
$app->startup($argv, $argc);