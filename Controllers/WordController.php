<?php
declare(strict_types=1);

namespace Controllers;


use Algorithms\Hyphenation;
use Core\API\Patterns;
use Core\API\Words;
use Views\WordsView;

class WordController
{
    private $wordService;
    private $algorithmService;
    private $patternService;

    public function __construct(Words $word,
                                Hyphenation $algorithm,
                                Patterns $pattern)
    {
        $this->wordService = $word;
        $this->algorithmService = $algorithm;
        $this->patternService = $pattern;
    }

    public function showAllWords()
    {
        if ($this->wordService->count() > 0) {
            return WordsView::renderJson(
                $this->wordService->read()
            );
        }
        return WordsView::renderJson([
            "message" => "No words found in database."
        ]);
    }

    public function showSingleWord(int $id)
    {
        if ($this->wordService->count() > 0) {
            if (!$this->wordService->find($id)) {
                return WordsView::renderJson([
                    "message" => "Word with id: {$id} not found"
                ]);
            }
            return WordsView::renderJson(
                $this->wordService->readSingle($id)
            );
        }
        return WordsView::renderJson([
            "message" => "No words found in database."
        ]);
    }

    public function createWord(array $data)
    {
        if ($this->patternService->count() === 0) {
            WordsView::renderJson([
                "message" => "Cannot add new word as there is no patterns list imported."
            ]);
            return;
        }

        if (!$this->validateData($data)) {
            WordsView::invalidData();
            return;
        }

        $creationData = [
            "word" => $data['word'],
            "hyphenated" => $this->algorithmService
                ->getResult($data['word']),
            "usedPatterns" => $this->algorithmService
                ->getValidPatternsForWord($data['word'])
        ];

        $this->wordService->create($creationData);
        WordsView::createdResponse();
        WordsView::renderJson([
            "result" => "Result for word {$data['word']}: {$creationData['hyphenated']}"
        ]);
    }

    public function deleteWord(int $id)
    {
        if (!$this->wordService->find($id)) {
            WordsView::invalidData();
            return;
        }

        $this->wordService->delete($id);
        WordsView::renderJson([
            "message" => "Word with id: {$id} deleted."
        ]);
    }

    private function validateData(array $data): bool
    {
        if (!isset($data['word'])) {
            return false;
        }
        return true;
    }
}