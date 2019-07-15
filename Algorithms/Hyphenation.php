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

class Hyphenation implements HyphenationInterface
{
    private $word;
    public $patterns = [];
    private $validPatterns = [];
    private $digitsInWord = [];
    private $completedWordWithDigits;

    // try to reduce vars number

    private $cache;
    private $db;
    private $logger;
    private $scan;

    public function __construct(FileCache $cache, Connection $db, Scan $scan, Logger $log)
    {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $log;
        $this->scan = $scan;

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings),
            Tools::CACHE_DEFAULT_EXPIRATION,
            Tools::CACHE_DIR_MODE,
            Tools::CACHE_FILE_MODE
        );
    }

    public function hyphenate(string $word): string
    {
        if ($this->cache->has($word)) {
            return (string)$this->cache->get($word);
        }

        if (Application::$settings['DEFAULT_SOURCE'] == Application::FILE_SOURCE) {
            return $this->getResult($word);
        }

        return $this->getFromDatabase($word);
    }

    private function getFromDatabase(string $word): string
    {
        $wordResult = "";

        if ($this->db->query("select id from patterns")->rowCount() == 0) {
            throw new \Exception("There's no available patterns in database.\n Please import patterns.\n Use: php startup -import patterns");
        }

        $query = $this->db->query(
            "select word, result from words inner join results on words.id = results.wordID where word = ?",
            [$word]
        );

        if ($query->rowCount() > 0) {
            foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $data) {
                $wordResult = $data['result'];
            }
        } else {
            $this->db->query("insert into words (word) values(?)", [$word]);
            $wordResult = $this->getResult($word);
            $this->db
                ->query("insert into results (wordID) select words.id from words where word = ?", [$word]);
            $this->db
                ->query("update results inner join words on words.id = results.wordID set result = ? where word = ?",
                    [$wordResult, $word]
                );
        }

        $this->cache->set($word, $wordResult);
        $this->getUsedPatterns($word);
        return $wordResult;
    }

    private function getResult(string $word): string
    {
        $this->clearVariables();

        if (Application::$settings['DEFAULT_SOURCE'] == Application::FILE_SOURCE) {
            $this->patterns = $this->scan->readDataFromFile(Application::$settings['PATTERNS_SOURCE']);
        } else if (Application::$settings['DEFAULT_SOURCE'] == Application::DB_SOURCE) {
            $this->patterns = $this->db->getPatterns();
        }

        $this->word = $word;

        $this->findValidPatterns();
        $this->pushDigitsToWord();
        $this->completeWordWithSyllables();
        $result = $this->addSyllableSymbols();
        $this->cache->set($word, $result);
        return $result;
    }

    private function clearVariables(): void
    {
        $this->word = null;
        $this->completedWordWithDigits = null;
        $this->validPatterns = [];
        $this->digitsInWord = [];
    }

    private function addSyllableSymbols(): string
    {
        $result = $this->completedWordWithDigits;
        for ($i = 0; $i < strlen($this->completedWordWithDigits); $i++) {
            $char = $this->completedWordWithDigits[$i];
            if (is_numeric($char)) {
                if ((int)$char % 2 > 0) {
                    if ($i != strlen($this->completedWordWithDigits) - 1)
                        $result = str_replace($char, '-', $result);
                    else
                        $result = str_replace($char, '', $result);
                } else
                    $result = str_replace($char, '', $result);
            }
        }
        return $result;
    }

    private function completeWordWithSyllables(): void
    {
        foreach (str_split($this->word) as $i => $char) {
            $this->completedWordWithDigits .= $char;
            if (isset($this->digitsInWord[$i]))
                $this->completedWordWithDigits .= $this->digitsInWord[$i];
        }
    }

    private function pushDigitsToWord(): void
    {
        foreach ($this->validPatterns as $pattern) {
            $digits_in_pattern = $this->extractDigitsFromWord($pattern);
            foreach ($digits_in_pattern as $position => $digit) {
                $position = $position + strpos($this->word, $this->clearPatternString($pattern));
                if (!isset($this->digits_in_word[$position]) || $this->digitsInWord[$position] < $digit)
                    $this->digitsInWord[$position] = $digit;
            }
        }
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
        $sql = "select patterns.pattern, patterns.id from patterns inner join valid_patterns vp on vp.patternID = id inner join words w on w.word = ? and w.id = vp.wordID";
        $query = $this->db->query($sql, [$word]);
        if ($query->rowCount() > 0) {
            foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $data) {
                $this->logger
                    ->log(LogLevel::INFO, "Pattern {pattern} used for word {word}",
                        ['pattern' => $data['pattern'], 'word' => $word]);
            }
        }
    }

    private function findValidPatterns(): void
    {
        foreach ($this->patterns as $pattern) {
            $cleanString = $this->clearPatternString($pattern);
            $position = strpos($this->word, $cleanString);

            if ($position === false ||
                ($pattern[0] == '.' && $position !== 0) ||
                ($pattern[strlen($pattern) - 1] == '.' && $position !== strlen($this->word) - strlen($cleanString)))
                continue;

            if (Application::$settings['DEFAULT_SOURCE'] == Application::DB_SOURCE) {
                $sql = "insert into valid_patterns (wordID, patternID) select w.id, p.id from words w inner join patterns p on p.pattern = ? and w.word = ?";
                $this->db->query($sql, [$pattern, $this->word]);
            }
            $this->validPatterns[] = $pattern;
        }
    }
}