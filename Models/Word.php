<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

class Word extends Model
{
    private $tableName = 'words';
    private $resultTable = 'results';

    private $id;
    private $word;
    private $hyphenatedWord;
    private $usedPatterns;


    public function id(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function patterns(array $patterns)
    {
        $this->usedPatterns = $patterns;
        return $this;
    }

    public function word(string $word)
    {
        $this->word = $word;
        return $this;
    }

    public function hyphenated(string $hyphen)
    {
        $this->hyphenatedWord = $hyphen;
        return $this;
    }

    public function usedPatterns()
    {
        return $this->usedPatterns;
    }

    public function count(): int
    {
        $sql = "SELECT id FROM words";
        $stmt = $this->connectionHandle
            ->getHandle()
            ->query($sql);
        return $stmt->rowCount();
    }

    public function find(): bool
    {
        if ($this->id !== null) {
            $sql = "SELECT id FROM {$this->tableName} WHERE id = {$this->id} LIMIT 0, 1";
        } else if ($this->word !== null) {
            $sql = "SELECT id FROM {$this->tableName} WHERE word = '{$this->word}' LIMIT 0, 1";
        }

        $statement = $this->connectionHandle
            ->getHandle()
            ->prepare($sql);

        $statement->execute();

        if ($statement->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function read()
    {
        if ($this->id !== null || $this->word !== null) {
            return $this->getByWordOrID();
        }

        $sql = "SELECT * FROM {$this->tableName}
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID
                ORDER BY {$this->tableName}.id DESC";
        $statement = $this->connectionHandle
            ->getHandle()
            ->query($sql);
        return $statement;
    }

    public function create(): bool
    {
        try {
            $this->connectionHandle
                ->getHandle()
                ->beginTransaction();

            $sql = "INSERT INTO `{$this->tableName}` (`word`) VALUES ('{$this->word}')";
            $this->connectionHandle
                ->getHandle()
                ->query($sql);

            $this->connectionHandle
                ->getHandle()
                ->query(
                    "INSERT INTO {$this->resultTable} (wordID) SELECT {$this->tableName}.id FROM {$this->tableName} WHERE word = '{$this->word}'");

            $this->connectionHandle
                ->getHandle()
                ->query(
                    "UPDATE {$this->resultTable} 
                        INNER JOIN {$this->tableName} ON {$this->tableName}.id = {$this->resultTable}.wordID 
                        SET result = '{$this->hyphenatedWord}' WHERE word = '{$this->word}'"
                );

            foreach ($this->usedPatterns as $pattern) {
                if ($this->commitValidPattern($pattern)) {
                    continue;
                }
            }

            $this->connectionHandle
                ->getHandle()
                ->commit();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function update(): bool
    {
        $sql = "UPDATE `{$this->tableName}` SET `word` = '{$this->word}' WHERE `id` = {$this->id}";
        $statement = $this->connectionHandle
            ->getHandle()
            ->query($sql);

        return $statement;
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM `{$this->tableName}` WHERE `id` = {$this->id}";
        $statement = $this->connectionHandle
            ->getHandle()
            ->query($sql);

        if ($statement)
            return true;
        return false;
    }

    private function commitValidPattern(string $pattern): bool
    {
        $sql = "insert into valid_patterns (wordID, patternID) 
                select w.id, p.id from {$this->tableName} w 
                inner join patterns p on p.pattern = '{$pattern}' and w.word = '{$this->word}'";
        $statement = $this->connectionHandle
            ->getHandle()
            ->query($sql);
        if ($statement)
            return true;
        return false;
    }

    private function getByWordOrID()
    {
        $sql = $this->getSQLForReading();
        $statement = $this->connectionHandle
            ->getHandle()
            ->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $this->word = $row['word'];
        $this->hyphenatedWord = $row['result'];
        return [
            "word" => $this->word,
            "result" => $this->hyphenatedWord
        ];
    }

    private function getSQLForReading()
    {
        if ($this->word !== null) {
            $sql = "SELECT * FROM {$this->tableName}
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID
                WHERE {$this->tableName}.word = '{$this->word}' LIMIT 0, 1";
        } else if ($this->id !== null) {
            $sql = "SELECT * FROM {$this->tableName}
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID
                WHERE {$this->tableName}.id = '{$this->id}' LIMIT 0, 1";
        }

        return $sql;
    }

}
