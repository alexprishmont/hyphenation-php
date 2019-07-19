<?php
declare(strict_types=1);

namespace Controllers;

use Algorithms\Hyphenation;
use Core\API\Words;
use Core\Controller;

class WordController extends Controller
{
    private $id;
    private $word;
    private $hyphenation;

    public function processRequest(Words $word, string $method, int $id, Hyphenation $hyphen)
    {
        $this->id = $id;
        $this->word = $word;
        $this->hyphenation = $hyphen;
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
                $response = $this->createWordFromRequest();
                break;
            case 'DELETE':
                $response = $this->deleteWord();
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
        $input = (array)json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!$this->validateInput($input))
            return $this->unprocessableEntityResponse();

        $hyphenated = $this->hyphenation->hyphenate($input['word']);

        $data = [
            "word" => $input['word'],
            "hyphenated" => $hyphenated,
            "usedPatterns" => $this->hyphenation->getValidPatternsForWord($input['word'])
        ];

        if ($this->word->create($data)) {
            return [
                'status_code_header' => 'HTTP/1.1 201 CREATED',
                'body' => json_encode(
                    [
                        "message" =>
                            "Word successfully hyphenated & added. [Word: {$input['word']} / Result: {$hyphenated}]"
                    ]
                )
            ];
        }
        return $this->notFoundResponse();
    }

    private function validateInput(array $data): bool
    {
        if (isset($data['word']))
            return true;
        return false;
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