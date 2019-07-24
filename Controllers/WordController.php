<?php
declare(strict_types=1);

namespace Controllers;


use Algorithms\Hyphenation;
use Models\Pattern;
use Models\Word;
use Views\WordsView;

class WordController
{
    private $wordService;
    private $algorithmService;
    private $patternService;

    public function __construct(Word $word,
                                Hyphenation $algorithm,
                                Pattern $pattern)
    {
        $this->wordService = $word;
        $this->algorithmService = $algorithm;
        $this->patternService = $pattern;
    }

    public function showAllWords()
    {
        if ($this->wordService->count() > 0) {
            $words = $this->wordService->read();
            $resultArray = [];
            $resultArray['data'] = [];
            while ($data = $words->fetch(\PDO::FETCH_ASSOC)) {
                array_push($resultArray['data'],
                    [
                        'id' => $data['id'],
                        'pattern' => $data['word'],
                        'hyphenated' => $data['result']
                    ]
                );
            }
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

        $hyphen = $this->algorithmService->getResult($data['word']);
        $usedPatterns = $this->algorithmService->getValidPatternsForWord($data['word']);

        $check = $this->wordService
            ->word($data['word'])
            ->hyphenated($hyphen)
            ->patterns($usedPatterns)
            ->create();

        if ($check) {
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