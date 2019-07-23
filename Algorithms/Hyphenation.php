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
use Core\Tools;
use Models\Pattern;
use Models\Word;

class Hyphenation implements HyphenationInterface
{
    private $word;
    private $validPatterns = [];

    private $cache;
    private $export;
    private $logger;
    private $scan;
    private $wordModel;
    private $patternModel;

    public function __construct(FileCache $cache,
                                Scan $scan,
                                Logger $log,
                                Word $wordModel,
                                Pattern $patternModel,
                                Export $export)
    {
        $this->cache = $cache;
        $this->logger = $log;
        $this->scan = $scan;
        $this->wordModel = $wordModel;
        $this->patternModel = $patternModel;
        $this->export = $export;

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings),
            Tools::CACHE_DEFAULT_EXPIRATION,
            Tools::CACHE_DIR_MODE,
            Tools::CACHE_FILE_MODE
        );
    }

    public function hyphenate(string $word): string
    {
        $this->word = $word;

        if ($this->cache->has($word)) {
            return (string)$this->cache->get($word);
        }

        if (Application::$settings['DEFAULT_SOURCE'] === Application::FILE_SOURCE) {
            $result = $this->getResult($word);
            $this->cache->set($word, $result);
            return $result;
        }

        return $this->getFromDatabase($word);
    }

    public function getValidPatternsForWord(string $word): array
    {
        $this->word = $word;
        $valid = $this->findValidPatterns(
            $this->getPatternsList()
        );
        return $valid;
    }

    public function getResult(string $word): string
    {
        $this->word = $word;
        $patterns = $this->getPatternsList();
        $this->validPatterns = $this->findValidPatterns($patterns);

        $result = $this->addSyllableSymbols(
            $this->completeWordWithDigits(
                $this->pushDigitsToWord($this->validPatterns)
            )
        );
        return $result;
    }

    private function getFromDatabase(string $word): string
    {
        if ($this->patternModel->count() === 0) {
            throw new \Exception('There is no available patterns in database.\n 
                                          Please import patterns.\n Use: php startup -import patterns');
        }

        $wordResult = $this->wordModel
            ->id(0)
            ->word($word)
            ->read()['result'];

        if ($wordResult !== null) {
            if (!$this->cache->has($word)) {
                $this->cache->set($word, $wordResult);
            }
            return $wordResult;
        }

        $wordResult = $this->insertWordIntoDatabase($word);
        $this->cache->set($word, $wordResult);
        $this->getUsedPatterns($word);
        return $wordResult;
    }

    private function insertWordIntoDatabase(string $word): string
    {
        $this->wordModel
            ->word($word)
            ->hyphenated($this->getResult($word))
            ->patterns($this->validPatterns)
            ->create();
        return $this->wordModel
            ->word($word)
            ->read()['result'];
    }

    private function addSyllableSymbols(string $completedWordWithDigits): string
    {
        $result = $completedWordWithDigits;
        for ($i = 0; $i < strlen($completedWordWithDigits); $i++) {
            $char = $completedWordWithDigits[$i];

            if (!is_numeric($char))
                continue;

            if ((int)$char % 2 > 0) {
                $result = str_replace($char, '-', $result);
            } else {
                $result = str_replace($char, '', $result);
            }
        }
        return $result;
    }

    private function completeWordWithDigits(array $digitsInWord = []): string
    {
        $completedWordWithDigits = "";
        foreach (str_split($this->word) as $i => $char) {
            $completedWordWithDigits .= $char;
            if (isset($digitsInWord[$i]))
                $completedWordWithDigits .= $digitsInWord[$i];
        }
        return $completedWordWithDigits;
    }

    private function pushDigitsToWord(array $validPatterns = []): array
    {
        $digitsInWord = [];
        foreach ($validPatterns as $pattern) {
            $digitsInPattern = $this->extractDigitsFromWord($pattern);
            foreach ($digitsInPattern as $position => $digit) {
                $position = $position + strpos($this->word, $this->clearPatternString($pattern));
                if (!isset($digitsInWord[$position]) || $digitsInWord[$position] < $digit)
                    $digitsInWord[$position] = $digit;
            }
        }
        return $digitsInWord;
    }

    private function extractDigitsFromWord(string $pattern): array
    {
        $digits = [];
        if (preg_match_all('/[0-9]+/', $pattern, $matches, PREG_OFFSET_CAPTURE) > 0) {
            $offset = preg_match('/[0-9]/', $pattern);
            foreach ($matches[0] as $match) {
                [$digit, $position] = $match;
                $position = $position - $offset;
                $offset = $offset + strlen($digit);
                $digits[$position] = (int)$digit;
            }
        }
        return $digits;
    }

    private function clearPatternString(string $pattern): string
    {
        $cleanString = preg_replace("/[^a-zA-Z]/", "", $pattern);
        $cleanString = substr($cleanString, 0, sizeof(str_split($pattern)));
        return trim(preg_replace("/\s+/", " ", $cleanString));
    }

    private function getUsedPatterns(string $word): void
    {
        foreach ($this->wordModel->usedPatterns() as $pattern) {
            $this->logger
                ->log(LogLevel::INFO,
                    "Pattern {pattern} used for word {word}",
                    ['pattern' => $pattern, 'word' => $word]);
        }
    }

    private function findValidPatterns(array $patterns): array
    {
        $validPatterns = [];
        foreach ($patterns as $pattern) {
            $cleanString = $this->clearPatternString($pattern);
            $position = strpos($this->word, $cleanString);

            if ($position === false ||
                ($pattern[0] == '.' && $position !== 0) ||
                ($pattern[strlen($pattern) - 1] == '.' && $position !== strlen($this->word) - strlen($cleanString)))
                continue;

            $validPatterns[] = $pattern;
        }
        return $validPatterns;
    }


    private function getPatternsList(): array
    {
        $patterns = [];
        if (Application::$settings['DEFAULT_SOURCE'] == Application::FILE_SOURCE) {
            $patterns = $this->scan->readDataFromFile(Application::$settings['PATTERNS_SOURCE']);
        } else if (Application::$settings['DEFAULT_SOURCE'] == Application::DB_SOURCE) {
            $patterns = $this->export->extractPatternsFromDatabase();
        }
        return $patterns;
    }

}