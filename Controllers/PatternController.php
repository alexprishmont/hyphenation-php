<?php
declare(strict_types=1);

namespace Controllers;

use Core\API\Patterns;
use Core\Controller;
use Views\PatternsView;

class PatternController extends Controller
{
    private $patternService;

    public function __construct(Patterns $patternAPI)
    {
        $this->patternService = $patternAPI;
    }

    public function showAllPatterns()
    {
        if ($this->patternService->count() > 0) {
            return PatternsView::renderJson(
                $this->patternService->read()
            );
        }
        return PatternsView::renderJson([
            "message" => "No patterns found in database."
        ]);
    }

    public function showSinglePattern(int $id)
    {
        if ($this->patternService->count() > 0) {
            if (!$this->patternService->find($id)) {
                return PatternsView::renderJson([
                    "message" => "Pattern with id: {$id} not found"
                ]);
            }
            return PatternsView::renderJson(
                $this->patternService->readSingle($id)
            );
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
        $this->patternService->create($data);
        PatternsView::createdResponse();
    }

    public function deletePattern(int $id)
    {
        if (!isset($id) || !$this->patternService->find($id)) {
            PatternsView::invalidData();
            return;
        }
        $this->patternService->delete($id);
        PatternsView::renderJson([
            "message" => "Pattern {$id} deleted."
        ]);
    }

    public function updatePattern(int $id, array $data)
    {
        if (!isset($id) ||
            !$this->validateData($data) ||
            !$this->patternService->find($id)) {
            PatternsView::invalidData();
            return;
        }
        $pattern = $data['pattern'];
        $this->patternService
            ->update([
                "id" => $id,
                "pattern" => $pattern
            ]);
        PatternsView::renderJson([
            "message" => "Pattern {$id} updated. New value: {$pattern}"
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