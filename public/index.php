<?php
$loader = require '../vendor/autoload.php';

use NXT\Application;

$app = new Application();

require('../app/Routes.php');