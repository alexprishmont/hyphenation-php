<?php
declare(strict_types=1);

namespace Algorithms;

use Algorithms\Interfaces\HyphenationInterface;
use Core\Application;
use Core\Cache\FileCache;
use Core\Database\Connection;
use Core\Log\Logger;
use Core\Log\LogLevel;
use Core\Scans\Scan;
use Core\Tools;
use Models\Pattern;
use Models\Word;

class Hyphenation implements HyphenationInterface
{
    private $word;

    private $cache;
    private $db;
    private $logger;
    private $scan;
    private $wordModel;
    private $patternModel;

    public function __construct(FileCache $cache,
                                Connection $db,
                                Scan $scan,
                                Logger $log,
                                Word $wordModel,
                                Pattern $patternModel)
    {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $log;
        $this->scan = $scan;
        $this->wordModel = $wordModel;
        $this->patternModel = $patternModel;

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings),
            Tools::CACHE_DEFAULT_EXPIRATION,
            Tools::CACHE_DIR_MODE,
            Tools::CACHE_FILE_MODE
        );
    }

    public function hyphenate(string $word): string
    {
        $this->word = $word;

        if (Application::apiStatus()) {
            return $this->getResult($word);
        }

        if ($this->cache->has($word)) {
            return (string)$this->cache->get($word);
        }

        if (Application::$settings['DEFAULT_SOURCE'] == Application::FILE_SOURCE) {
            return $this->getResult($word);
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

    private function getFromDatabase(string $word): string
    {
        if ($this->patternModel->count() == 0) {
            throw new \Exception("There's no available patterns in database.\n 
                                          Please import patterns.\n Use: php startup -import patterns");
        }

        $this->wordModel->word = $word;
        $this->wordModel->readSingleByWord();
        $wordResult = $this->wordModel->hyphenatedWord;

        if ($wordResult !== "") {
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
        $this->wordModel->word = $word;
        $this->wordModel->hyphenatedWord = $this->getResult($word);
        $this->wordModel->create();
        return $this->wordModel->hyphenatedWord;
    }

    private function getResult(string $word): string
    {
        $patterns = $this->getPatternsList();
        $valid = $this->findValidPatterns($patterns);

        $this->wordModel->usedPatterns = $valid;

        $result = $this->addSyllableSymbols(
            $this->completeWordWithDigits(
                $this->pushDigitsToWord($valid)
            )
        );
        $this->cache->set($word, $result);
        return $result;
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
        $sql = "select patterns.pattern, patterns.id from patterns 
                inner join valid_patterns vp on vp.patternID = id 
                inner join words w on w.word = ? and w.id = vp.wordID";
        $query = $this->db->query($sql, [$word]);
        if ($query->rowCount() > 0) {
            foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $data) {
                $this->logger
                    ->log(LogLevel::INFO,
                        "Pattern {pattern} used for word {word}",
                        ['pattern' => $data['pattern'], 'word' => $word]);
            }
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
            $patterns = $this->db->getPatterns();
        }
        return $patterns;
    }

}