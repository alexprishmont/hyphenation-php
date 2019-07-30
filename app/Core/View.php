<?php
declare(strict_types=1);

namespace NXT\Core;

class View
{
    public static function create(string $template, array $data = [])
    {
        $instance = Twig::getInstance();
        $loadedTemplate = $instance->getTemplates();

        return $loadedTemplate[$template]->render($data);
    }

    public static function renderJson(array $data)
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE');
        header('Access-Control-Max-Age: 3600');
        header('Access-Control-Allow-Headers: Content-Type, 
            Access-Control-Allow-Headers, 
            Authorization, 
            X-Requested-With'
        );
        return print(json_encode($data));
    }

    public static function invalidData()
    {
        self::renderHTTPException([
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => [
                'message' => 'Wrong input.'
            ]
        ]);
    }

    public static function unhandledError()
    {
        self::renderHTTPException([
            'status_code_header' => 'HTTP/1.1 409 Conflict',
            'body' => ['message' => 'Something went wrong.. Try again.']
        ]);
    }

    public static function notFound(string $customBody = null)
    {
        self::renderHTTPException([
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => ($customBody === null) ?
                ['message' => 'Such element not found.'] : ['message' => $customBody]
        ]);
    }

    public static function createdResponse()
    {
        self::renderHTTPException([
            'status_code_header' => 'HTTP/1.1 201 CREATED',
            'body' => [
                'message' => 'Creation successfully proceeded.'
            ]
        ]);
    }

    public static function renderHTTPException(array $response)
    {
        header($response['status_code_header']);
        if ($response['body']) {
            self::renderJson($response['body']);
        }
    }
}