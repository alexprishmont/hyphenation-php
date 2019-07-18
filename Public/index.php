<?php
require_once("../Core/Autoloader.php");
Core\Autoloader::register();

$app = new Core\Application([], 0);
$app->api(true);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$method = $_SERVER['REQUEST_METHOD'];

$id = 0;
if (isset($uri[2])) {
    $id = (int)$uri[2];
}

switch ($uri[1]) {
    case 'pattern':
        $app->getInstance('patternController')
            ->processRequest(
                $app->getInstance('patternsAPI'),
                $method,
                $id
            );
        break;

    case 'word':
        $app->getInstance('wordController')
            ->processRequest(
                $app->getInstance('wordsAPI'),
                $method,
                $id
            );
        break;

    default:
        http_response_code(404);
        exit();
        break;
}