<?php
require_once("../Core/Autoloader.php");
Core\Autoloader::register();

use Core\Router\Router;

$app = new Core\Application([], 0);

Router::add('/', function () {
    // TODO: homepage
});

Router::add('/pattern', function () {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->showAllPatterns();
});

Router::add('/pattern/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->showSinglePattern($id);
});

Router::add('/pattern', function () {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->createPattern($data);
}, 'post');

Router::add('/pattern/([0-9]*)', function ($id) {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->updatePattern($id, $data);
}, 'put');

Router::add('/pattern/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->deletePattern($id);
}, 'delete');

Router::add('/word', function () {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->showAllWords();
});

Router::add('/word/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->showSingleWord($id);
});

Router::add('/word', function () {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->createWord($data);
}, 'post');

Router::add('/word/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->deleteWord($id);
}, 'delete');

Router::run('/');