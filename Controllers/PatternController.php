<?php
declare(strict_types=1);

namespace Controllers;

use Core\API\Patterns;
use Core\Controller;

class PatternController extends Controller
{
    private $id;
    private $pattern;

    public function processRequest(Patterns $pattern, string $method, int $id)
    {
        $this->id = $id;
        $this->pattern = $pattern;
        switch ($method) {
            case 'GET':
                if ($this->id === 0) {
                    $response['body'] = $pattern->read();
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                } else {
                    $response['body'] = $pattern->readSingle($this->id);
                    $response['status_code_header'] = 'HTTP/1.1 200 OK';
                }
                break;
            case 'POST':
                $response = $this->createPatternFromRequest();
                break;
            case 'DELETE':
                $response = $this->deletePattern();
                break;
            case 'PUT':
                $response = $this->updatePattern();
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

    private function updatePattern(): array
    {
        $input = (array)json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!$this->validateInputForUpdate($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->id = $input['id'];

        if (!$this->pattern->find($this->id)) {
            return $this->notFoundResponse();
        }

        $this->pattern->update($input);
        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode(
                ["message" => "Pattern [id: {$this->id}] successfully updated."]
            )
        ];
    }

    private function deletePattern(): array
    {
        if (!$this->pattern->find($this->id)) {
            return $this->notFoundResponse();
        }

        if ($this->pattern->delete($this->id))
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode(
                    ["message" => "Pattern [id: {$this->id}] deleted."]
                )
            ];
        else
            return [
                'status_code_header' => 'HTTP/1.1 404 Not Found',
                'body' => json_encode(
                    ["message" => "Error while trying to delete pattern. [id: {$this->id}]"]
                )
            ];
    }

    private function createPatternFromRequest(): array
    {
        $input = (array)json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!$this->validateInputForCreation($input)) {
            return $this->unprocessableEntityResponse();
        }

        $this->pattern->create($input);

        $response = [
            'status_code_header' => 'HTTP/1.1 201 CREATED',
            'body' => json_encode(
                ["message" => "New pattern successfully created."]
            )
        ];
        return $response;
    }

    private function validateInputForUpdate(array $data): bool
    {
        if (!isset($data['pattern']) || !isset($data['id']))
            return false;
        return true;
    }

    private function validateInputForCreation(array $data): bool
    {
        if (!isset($data['pattern'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse(): array
    {
        return [
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode([
                "message" => "Wrong input."
            ])
        ];
    }

    private function notFoundResponse(): array
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(
                ["message" => "Such response method not found."]
            )
        ];
    }
}