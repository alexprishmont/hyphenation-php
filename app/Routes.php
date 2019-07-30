<?php

use NXT\Core\Router\Route;

Route::add('/', function () use ($app) {
    return $app->getInstance('indexController')->index();
});

Route::add('/patterns', function () use ($app) {
    return $app->getInstance('patternsController')->index();
});

Route::add('/patterns/([0-9]*)', function ($page) use ($app) {
    return $app->getInstance('patternsController')->page($page);
});

Route::add('/patterns/create', function () use ($app) {
    return $app->getInstance('patternsController')->create();
});

Route::add('/patterns/create', function () use ($app) {
    return $app->getInstance('patternsController')->create($_POST);
}, 'post');

Route::add('/patterns/delete/([0-9]*)', function ($id) use ($app) {
    return $app->getInstance('patternsController')->delete($id);
});

Route::add('/words', function () use ($app) {
    return $app->getInstance('wordsController')->index();
});

Route::add('/words/([0-9]*)', function ($page) use ($app) {
    return $app->getInstance('wordsController')->page($page);
});

Route::add('/words/create', function () use ($app) {
    return $app->getInstance('wordsController')->create();
});

Route::add('/words/create', function () use ($app) {
    return $app->getInstance('wordsController')->create($_POST);
}, 'post');

Route::add('/words/delete/([0-9]*)', function ($id) use ($app) {
    return $app->getInstance('wordsController')->delete($id);
});

// API Routes
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

Route::run('/visma-internship');