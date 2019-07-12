<?php
declare(strict_types=1);

namespace Algorithms;

use Algorithms\Interfaces\AlgorithmInterface;
use Core\Application;
use Core\Cache\FileCache;
use Core\Database\Connection;
use Core\Scans\Scan;
use Core\Tools;

class Hyphenation implements AlgorithmInterface
{
    private $word;
    public $patterns = [];
    private $validPatterns = [];
    private $digitsInWord = [];
    private $completedWordWithDigits;

    private $cache;
    private $db;

    public function __construct(FileCache $cache, Connection $db, Scan $scan)
    {
        $this->db = $db;
        $this->cache = $cache;

        if (Application::$settings['DEFAULT_SOURCE'] == Application::FILE_SOURCE) {
            $this->patterns = $scan->readDataFromFile(Application::$settings['PATTERNS_SOURCE']);
        } else if (Application::$settings['DEFAULT_SOURCE'] == Application::DB_SOURCE) {
            $this->patterns = $db->getPatterns();
        }

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings),
            Tools::CACHE_DEFAULT_EXPIRATION,
            Tools::CACHE_DIR_MODE,
            Tools::CACHE_FILE_MODE
        );
    }

    public function hyphenate(string $word): string
    {
        if (!$this->cache->has($word)) {
            if (Application::$settings['DEFAULT_SOURCE'] == Application::DB_SOURCE) {
                $query = $this->db->query("select word from results where result_for = ? limit 1", [$word]);
                if ($query->rowCount() > 0) {
                    $result = $query->fetchAll(\PDO::FETCH_ASSOC);
                    $word_result = "";
                    foreach ($result as $data) {
                        $word_result = $data['word'];
                    }
                    $this->cache->set($word, $word_result);
                    return $word_result;
                } else {
                    $result = $this->getResult($word);
                    $this->db->query("insert into results (word, result_for) values (?, ?)", [$result, $word]);
                    return $result;
                }
            } else {
                return $this->getResult($word);
            }
        } else {
            return (string)$this->cache->get($word);
        }
    }

    private function getResult(string $word): string
    {
        $this->clearVariables();
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

    private function findValidPatterns(): void
    {
        if (Application::$settings['DEFAULT_SOURCE'] == Application::DB_SOURCE) {
            $query = $this->db->query("select pattern from valid_patterns where valid_for = ?", $this->word);
            if ($query->rowCount() > 0) {
                foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $data) {
                    $this->validPatterns[] = $data['pattern'];
                }
                return;
            }
        }

        foreach ($this->patterns as $pattern) {
            $cleanString = $this->clearPatternString($pattern);
            $position = strpos($this->word, $cleanString);

            if ($position === false ||
                ($pattern[0] == '.' && $position !== 0) ||
                ($pattern[strlen($pattern) - 1] == '.' && $position !== strlen($this->word) - strlen($cleanString)))
                continue;

            if (Application::$settings['DEFAULT_SOURCE'] == Application::DB_SOURCE) {
                $this->db
                    ->query('insert into valid_patterns (pattern, valid_for) values (?, ?)', [$pattern, $this->word]);
            }
            $this->validPatterns[] = $pattern;
        }
    }
}