<?php
declare(strict_types=1);

namespace NXT\Controllers;

use NXT\Algorithms\Hyphenation;
use NXT\Algorithms\Proxy;
use NXT\Core\Controller;
use NXT\Core\View;
use NXT\Models\Word;

class WordsPageController extends Controller
{
    private $service;
    private $algorithm;

    public function __construct(Word $word, Proxy $proxy)
    {
        $this->service = $word;
        $this->algorithm = $proxy;
    }

    public function index(): void
    {
        $words = $this->service->getByPage(1);
        echo View::create('words', [
                'words' => $words,
                'currentPage' => 1,
                'pages' => $this->service->getPageCount()
            ]
        );
    }

    public function page(int $page): void
    {
        $words = $this->service->getByPage($page);
        echo View::create('words', [
                'words' => $words,
                'currentPage' => $page,
                'pages' => $this->service->getPageCount()
            ]
        );
    }

    public function create(array $data = null): void
    {
        if (!isset($data)) {
            echo View::create('words_create');
            return;
        }

        if (!$this->validatePOST($data)) {
            echo View::create('words_create', ['failure' => true]);
            return;
        }

        if ($this->service->find($data['word'])) {
            echo View::create('words_create',
                [
                    'found' => true,
                    'body' => $data['word']
                ]
            );
            return;
        }

        $result = $this->algorithm->hyphenate($data['word']);

        echo View::create('words_create',
            [
                'success' => true,
                'body' => 'Word: ' . $data['word'] . '/ Result: ' . $result
            ]
        );
    }

    public function delete(int $id): void
    {
        if (!$this->service->find($id)) {
            echo View::create('words',
                [
                    'words' => $this->service->getByPage(1),
                    'deleteFailure' => true,
                    'currentPage' => 1,
                    'pages' => $this->service->getPageCount(),
                    'body' => $id
                ]
            );
            return;
        }

        echo View::create('words',
            [
                'words' => $this->service->getByPage(1),
                'deleteSuccess' => true,
                'currentPage' => 1,
                'pages' => $this->service->getPageCount(),
                'body' => $id,
                'word' => $this->service->id($id)->read()['word']
            ]
        );

        $this->service
            ->id($id)
            ->delete();
    }

    private function validatePOST(array $post): bool
    {
        if (!isset($post) || empty($post)) {
            return false;
        }

        if (!isset($post['word']) || empty($post['word'])) {
            return false;
        }

        return true;
    }
}