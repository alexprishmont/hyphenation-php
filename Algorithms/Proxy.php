<?php
declare(strict_types=1);

namespace Algorithms;

use Algorithms\Interfaces\HyphenationInterface;
use Core\Application;
use Core\Cache\FileCache;
use Core\Database\Export;
use Core\Log\Logger;
use Core\Log\LogLevel;
use Core\Scans\Scan;
use Models\Pattern;
use Models\Word;

class Proxy implements HyphenationInterface
{
    private $cache;
    private $wordModel;
    private $patternModel;
    private $scan;

    public function __construct(Word $word,
                                Pattern $pattern,
                                Scan $scan)
    {
        $this->cache = FileCache::getInstanceOf();
        $this->wordModel = $word;
        $this->patternModel = $pattern;
        $this->scan = $scan;
    }

    public function hyphenate(string $word): string
    {
        if ($this->cache->has($word)) {
            return (string)$this->cache->get($word);
        }

        $dbCheck = $this->wordModel
            ->word($word)
            ->find();

        if ($dbCheck) {
            return $this->wordModel
                ->word($word)
                ->read()['result'];
        }

        $hyphenation = new Hyphenation($this->getPatterns());
        $result = $hyphenation->hyphenate($word);

        $this->cache->set($word, $result);
        $this->wordModel
            ->word($word)
            ->hyphenated($result)
            ->patterns($hyphenation->getValidPatternsForWord($word))
            ->create();

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