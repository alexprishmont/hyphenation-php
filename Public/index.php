<?php
require_once('../Core/Autoloader.php');
Core\Autoloader::register();

$app = new Core\Application([]);

require('Routes.php');