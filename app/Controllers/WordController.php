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

        WordsView::notFound('No words found in database.');
        return false;
    }

    public function showSingleWord(int $id)
    {
        if ($this->wordService->count() > 0) {
            $check = $this->wordService->find($id);
            if (!$check) {
                WordsView::notFound('Word with id: ' . $id . ' not found.');
                return false;
            }
            return WordsView::renderJson([
                'data' => [
                    'id' => $id,
                    'word' => $this->wordService->id($id)->read()['word'],
                    'hyphenated' => $this->wordService->id($id)->read()['result']
                ]
            ]);
        }
        WordsView::notFound('No words found in database.');
    }

    public function createWord(array $data)
    {
        if ($this->patternService->count() === 0) {
            WordsView::notFound('Cannot add new word to database as there is no patterns list imported.');
            return;
        }

        if (!$this->validateData($data)) {
            WordsView::invalidData();
            return;
        }

        $check = $this->wordService->find($data['word']);

        if ($check) {
            WordsView::invalidData();
            return;
        }

        $check = $this->algorithmService->hyphenate($data['word']);

        if ($check !== null) {
            WordsView::createdResponse();
            return;
        }
        WordsView::unhandledError();
    }

    public function deleteWord(int $id)
    {
        $check = $this->wordService->find($id);

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