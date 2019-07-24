<?php
declare(strict_types=1);

namespace Core;


class View
{
    public static function renderJson(array $data)
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE');
        header('Access-Control-Max-Age: 3600');
        header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
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

    public static function createdResponse()
    {
        self::renderHTTPException([
            'status_code_header' => 'HTTP/1.1 201 CREATED',
            'body' => [
                'message' => 'Creation successfully proceeded.'
            ]
        ]);
    }

    private static function renderHTTPException(array $response)
    {
        header($response['status_code_header']);
        if ($response['body']) {
            self::renderJson($response['body']);
        }
    }
}