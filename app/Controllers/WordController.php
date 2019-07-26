<?php
declare(strict_types=1);

namespace NXT\Controllers;

use NXT\Algorithms\Proxy;
use NXT\Models\Pattern;
use NXT\Models\Word;
use NXT\Views\WordsView;

class WordController
{
    private $wordService;
    private $algorithmService;
    private $patternService;

    public function __construct(Word $word,
                                Proxy $algorithm,
                                Pattern $pattern)
    {
        $this->wordService = $word;
        $this->algorithmService = $algorithm;
        $this->patternService = $pattern;
    }

    public function showAllWords()
    {
        if ($this->wordService->count() > 0) {
            $resultArray = $this->wordService->getAll();
            return WordsView::renderJson($resultArray);
        }
        return WordsView::renderJson([
            'message' => 'No words found in database.'
        ]);
    }

    public function showSingleWord(int $id)
    {
        if ($this->wordService->count() > 0) {
            $check = $this->wordService
                ->id($id)
                ->find();
            if (!$check) {
                return WordsView::renderJson([
                    'message' => 'Word with id: ' . $id . ' not found'
                ]);
            }
            return WordsView::renderJson([
                'data' => [
                    'id' => $id,
                    'word' => $this->wordService->id($id)->read()['word'],
                    'hyphenated' => $this->wordService->id($id)->read()['result']
                ]
            ]);
        }
        return WordsView::renderJson([
            'message' => 'No words found in database.'
        ]);
    }

    public function createWord(array $data)
    {
        if ($this->patternService->count() === 0) {
            WordsView::renderJson([
                'message' => 'Cannot add new word as there is no patterns list imported.'
            ]);
            return;
        }

        if (!$this->validateData($data)) {
            WordsView::invalidData();
            return;
        }

        $check = $this->wordService
            ->word($data['word'])
            ->find();

        if ($check) {
            WordsView::invalidData();
            return;
        }

        $check = $this->algorithmService->hyphenate($data['word']);

        if ($check !== null) {
            WordsView::createdResponse();
            return;
        }
        echo WordsView::renderJson(['message' => 'Something went wrong...']);
    }

    public function deleteWord(int $id)
    {
        $check = $this->wordService
            ->id($id)
            ->find();

        if (!$check) {
            WordsView::invalidData();
            return;
        }

        $this->wordService
            ->id($id)
            ->delete();

        WordsView::renderJson([
            'message' => 'Word with id: ' . $id . ' deleted.'
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