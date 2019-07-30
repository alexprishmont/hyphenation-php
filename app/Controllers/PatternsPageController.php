<?php
declare(strict_types=1);

namespace NXT\Controllers;

use NXT\Core\Controller;
use NXT\Core\View;
use NXT\Models\Pattern;

class PatternsPageController extends Controller
{
    private $service;

    public function __construct(Pattern $pattern)
    {
        $this->service = $pattern;
    }

    public function index(): void
    {
        $patterns = $this->service->getByPage(1);
        echo View::create('patterns', [
                'patterns' => $patterns,
                'currentPage' => 1,
                'pages' => $this->service->getPageCount()
            ]
        );
    }

    public function page(int $page): void
    {
        $patterns = $this->service->getByPage($page);
        echo View::create('patterns', [
                'patterns' => $patterns,
                'currentPage' => $page,
                'pages' => $this->service->getPageCount()
            ]
        );
    }

    public function create(array $data = null): void
    {
        if (!isset($data)) {
            echo View::create('patterns_create');
            return;
        }

        if (!$this->validatePOST($data)) {
            echo View::create('patterns_create', ['failure' => true]);
            return;
        }

        $this->service
            ->pattern($data['pattern'])
            ->create();

        echo View::create('patterns_create',
            [
                'success' => true,
                'body' => $data['pattern']
            ]
        );
    }

    public function delete(int $id): void
    {
        if (!$this->service->find($id)) {
            echo View::create('patterns',
                [
                    'patterns' => $this->service->getByPage(1),
                    'currentPage' => 1,
                    'pages' => $this->service->getPageCount(),
                    'deleteFailure' => true,
                    'body' => $id
                ]
            );
            return;
        }

        $this->service
            ->id($id)
            ->delete();

        echo View::create('patterns',
            [
                'patterns' => $this->service->getByPage(1),
                'deleteSuccess' => true,
                'currentPage' => 1,
                'pages' => $this->service->getPageCount(),
                'body' => $id
            ]
        );
    }

    private function validatePOST(array $post): bool
    {
        if (!isset($post) || empty($post)) {
            return false;
        }

        if (!isset($post['pattern']) || empty($post['pattern'])) {
            return false;
        }

        return true;
    }
}