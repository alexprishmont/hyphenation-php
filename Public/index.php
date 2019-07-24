<?php
require_once('../Core/Autoloader.php');
Core\Autoloader::register();

$app = new Core\Application([], 0);

require('Routes.php');