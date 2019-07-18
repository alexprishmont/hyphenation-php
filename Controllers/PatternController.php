<?php
declare(strict_types=1);

namespace Controllers;

use Core\API\Patterns;
use Core\Controller;

class PatternController extends Controller
{
    private $id;
    private $data;

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function processRequest(Patterns $pattern, string $method)
    {
        switch ($method) {
            case 'GET':
                if (!$this->id) {
                    $response['body'] = $pattern->read();
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                } else {
                    $response['body'] = $pattern->readSingle($this->id);
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                }
                break;
            case 'POST':
                break;
            case 'DELETE':
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}