<?php
declare(strict_types=1);

namespace NXT\Controllers;

use NXT\Core\Controller;
use NXT\Models\Pattern;
use NXT\Views\PatternsView;

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
            $resultArray = $this->patternService->getAll();
            return PatternsView::renderJson($resultArray);
        }
        PatternsView::notFound();
    }

    public function showSinglePattern(int $id)
    {
        if ($this->patternService->count() > 0) {
            $check = $this->patternService->find($id);
            if (!$check) {
                PatternsView::notFound('Pattern with id: ' . $id . ' not found.');
                return false;
            }
            return PatternsView::renderJson([
                'data' => [
                    'id' => $id,
                    'pattern' => $this->patternService
                        ->id($id)
                        ->read()
                ]
            ]);
        }
        PatternsView::notFound();
    }

    public function createPattern(array $data)
    {
        if (!$this->validateData($data)) {
            PatternsView::invalidData();
            return;
        }

        $check = $this->patternService->find($data['pattern']);

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

        $check = $this->patternService->find($id);

        if (!$check) {
            PatternsView::invalidData();
            return;
        }

        $this->patternService
            ->id($id)
            ->delete();

        PatternsView::renderJson([
            'message' => 'Pattern ' . $id . ' deleted.'
        ]);
    }

    public function updatePattern(int $id, array $data)
    {
        if (!isset($id) || !$this->validateData($data)) {
            PatternsView::invalidData();
            return;
        }

        $check = $this->patternService->find($id);

        if (!$check) {
            PatternsView::invalidData();
            return;
        }

        $this->patternService
            ->id($id)
            ->pattern($data['pattern'])
            ->update();
        PatternsView::renderJson([
            'message' => 'Pattern ' . $id . ' updated. New value ' . $data['pattern']
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