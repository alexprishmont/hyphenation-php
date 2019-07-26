<?php

use NXT\Core\Router\Route;

Route::add('/', function () {
    // TODO: homepage
});

Route::add('/api/pattern', function () {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->showAllPatterns();
});

Route::add('/api/pattern/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->showSinglePattern($id);
});

Route::add('/api/pattern', function () {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->createPattern($data);
}, 'post');

Route::add('/api/pattern/([0-9]*)', function ($id) {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->updatePattern($id, $data);
}, 'put');

Route::add('/api/pattern/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('patternController');
    return $controller->deletePattern($id);
}, 'delete');

Route::add('/api/word', function () {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->showAllWords();
});

Route::add('/api/word/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->showSingleWord($id);
});

Route::add('/api/word', function () {
    $data = (array)json_decode(
        file_get_contents("php://input"),
        true
    );
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->createWord($data);
}, 'post');

Route::add('/api/word/([0-9]*)', function ($id) {
    global $app;
    $controller = $app->getInstance('wordController');
    return $controller->deleteWord($id);
}, 'delete');

Route::run('/');