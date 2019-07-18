<?php
declare(strict_types=1);

namespace Controllers;

use Core\API\Words;
use Core\Controller;

class WordController extends Controller
{
    private $id;
    private $word;

    public function processRequest(Words $word, string $method, int $id)
    {
        $this->id = $id;
        $this->word = $word;
        switch ($method) {
            case 'GET':
                if ($this->id === 0) {
                    $response = [
                        'status_code_header' => 'HTTP/1.1 200 OK',
                        'body' => $this->word->read()
                    ];
                } else {
                    if ($this->word->find($this->id)) {
                        $response = [
                            'status_code_header' => 'HTTP/1.1 200 OK',
                            'body' => $this->word->readSingle($this->id)
                        ];
                    } else {
                        $response = $this->notFoundResponse();
                    }
                }
                break;
            case 'POST':

                break;
            case 'DELETE':
                $response = $this->deleteWord();
                break;
            case 'PUT':

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

    private function updateWord(): array
    {

    }

    private function deleteWord(): array
    {
        if (!$this->word->find($this->id)) {
            return $this->notFoundResponse();
        }
        
        if ($this->word->delete($this->id)) {
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode(
                    ["message" => "Word [id: {$this->id}] successfully deleted."]
                )
            ];
        } else {
            return [
                'status_code_header' => 'HTTP/1.1 404 Not Found',
                'body' => json_encode(
                    ["message" => "Error while trying to delete word. [id: {$this->id}]"]
                )
            ];
        }
    }

    private function createWordFromRequest(): array
    {

    }

    private function validateInputForUpdate(array $data): bool
    {

    }

    private function validateInputForCreation(array $data): bool
    {

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