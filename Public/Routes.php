<?php

use Core\Router\Route;

Route::add('/', function () {
    // TODO: homepage
});

Route::add('/pattern', function () {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->showAllPatterns();
});

Route::add('/pattern/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->showSinglePattern($id);
});

Route::add('/pattern', function () {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->createPattern($data);
}, 'post');

Route::add('/pattern/([0-9]*)', function ($id) {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->updatePattern($id, $data);
}, 'put');

Route::add('/pattern/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->deletePattern($id);
}, 'delete');

Route::add('/word', function () {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->showAllWords();
});

Route::add('/word/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->showSingleWord($id);
});

Route::add('/word', function () {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->createWord($data);
}, 'post');

Route::add('/word/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->deleteWord($id);
}, 'delete');

Route::run('/');