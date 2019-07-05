<?php
require_once("Core/Autoloader.php");

Core\Autoloader::register();

$app = new Core\Main($argv, $argc);
$app->startup();