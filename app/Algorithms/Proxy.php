<?php
declare(strict_types=1);

namespace NXT\Algorithms;

use NXT\Algorithms\Interfaces\HyphenationInterface;
use NXT\Application;
use NXT\Core\Cache\FileCache;
use NXT\Core\Database\Export;
use NXT\Core\Scans\Scan;
use NXT\Models\Pattern;
use NXT\Models\Word;

class Proxy implements HyphenationInterface
{
    private $cache;
    private $wordModel;
    private $patternModel;
    private $scan;
    private $hyphenation;

    public function __construct(
        Word $word,
        Pattern $pattern,
        Scan $scan
    ) {
        $this->cache = FileCache::getInstanceOf();
        $this->wordModel = $word;
        $this->patternModel = $pattern;
        $this->scan = $scan;

        $this->hyphenation = new Hyphenation($this->getPatterns());
    }

    public function hyphenate(string $word): string
    {
        if ($this->cache->has($word)) {
            return (string)$this->cache->get($word);
        }

        $dbCheck = $this->wordModel->find($word);

        if ($dbCheck) {
            $result = $this->wordModel
                ->word($word)
                ->read()['result'];

            $this->cache->set($word, $result);
            return $result;
        }

        $result = $this->hyphenation->hyphenate($word);

        $this->wordModel
            ->word($word)
            ->hyphenated($result)
            ->patterns($this->hyphenation->getValidPatternsForWord($word))
            ->create();

        $this->cache->set($word, $result);
        return $result;
    }

    private function getPatterns(): array
    {
        if ($this->patternModel->count() > 0) {
            return Export::getInstanceOf()->extractPatternsFromDatabase();
        }
        return $this->scan->readDataFromFile(Application::$settings['PATTERNS_SOURCE']);
    }
}