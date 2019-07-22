<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Models\Pattern;
use Views\PatternsView;

class PatternController extends Controller
{
    private $patternService;

    public function __construct(Pattern $pattern)
    {
        $this->patternService = $pattern;
    }

    public function showAllPatterns()
    {
        if ($this->patternService->count() > 0) {
            $patterns = $this->patternService->read();
            $resultArray = [];
            $resultArray['data'] = [];
            while ($data = $patterns->fetch(\PDO::FETCH_ASSOC)) {
                array_push($resultArray['data'],
                    [
                        "id" => $data['id'],
                        "pattern" => $data['pattern']
                    ]
                );
            }
            return PatternsView::renderJson($resultArray);
        }
        return PatternsView::renderJson([
            "message" => "No patterns found in database."
        ]);
    }

    public function showSinglePattern(int $id)
    {
        if ($this->patternService->count() > 0) {
            $check = $this->patternService
                ->id($id)
                ->find();
            if (!$check) {
                return PatternsView::renderJson([
                    "message" => "Pattern with id: {$id} not found"
                ]);
            }
            return PatternsView::renderJson([
                "data" => [
                    "id" => $id,
                    "pattern" => $this->patternService
                        ->id($id)
                        ->read()
                ]
            ]);
        }
        return PatternsView::renderJson([
            "message" => "No patterns found in database."
        ]);
    }

    public function createPattern(array $data)
    {
        if (!$this->validateData($data)) {
            PatternsView::invalidData();
            return;
        }

        $check = $this->patternService
            ->pattern($data['pattern'])
            ->find();

        if ($check) {
            PatternsView::invalidData();
            return;
        }
        $this->patternService
            ->pattern($data['pattern'])
            ->create();
        PatternsView::createdResponse();
    }

    public function deletePattern(int $id)
    {
        if (!isset($id)) {
            PatternsView::invalidData();
            return;
        }

        $check = $this->patternService
            ->id($id)
            ->find();

        if (!$check) {
            PatternsView::invalidData();
            return;
        }

        $this->patternService
            ->id($id)
            ->delete();

        PatternsView::renderJson([
            "message" => "Pattern {$id} deleted."
        ]);
    }

    public function updatePattern(int $id, array $data)
    {
        if (!isset($id) || !$this->validateData($data)) {
            PatternsView::invalidData();
            return;
        }

        $check = $this->patternService
            ->id($id)
            ->find();

        if (!$check) {
            PatternsView::invalidData();
            return;
        }

        $this->patternService
            ->id($id)
            ->pattern($data['pattern'])
            ->update();
        PatternsView::renderJson([
            "message" => "Pattern {$id} updated. New value {$data['pattern']}"
        ]);
    }

    private function validateData(array $data): bool
    {
        if (!isset($data['pattern'])) {
            return false;
        }
        return true;
    }
}