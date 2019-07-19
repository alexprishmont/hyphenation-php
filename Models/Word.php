<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

class Word extends Model
{
    private $tableName = "words";
    private $resultTable = "results";

    public $id = 0;
    public $word = "";
    public $hyphenatedWord = "";
    public $usedPatterns = [];

    public function readSingleByWord(): void
    {
        $sql = "SELECT * FROM {$this->tableName} 
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID 
                WHERE word = ?";

        $statement = $this->connectionHandle
            ->query($sql, [$this->word]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $this->word = $row['word'];
            $this->hyphenatedWord = $row['result'];
            $this->id = $row['id'];
        }
    }

    public function find(): bool
    {
        $statement = $this->connectionHandle
            ->query("SELECT id FROM {$this->tableName} WHERE id = ? LIMIT 0, 1", [$this->id]);
        if ($statement->rowCount() > 0)
            return true;
        return false;
    }

    public function read(): object
    {
        $sql = "SELECT * FROM {$this->tableName}
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID
                ORDER BY {$this->tableName}.id DESC";
        $statement = $this->connectionHandle
            ->query($sql);
        return $statement;
    }

    public function readSingle(): void
    {
        $sql = "SELECT * FROM {$this->tableName}
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID
                WHERE {$this->tableName}.id = ? LIMIT 0, 1";
        $statement = $this->connectionHandle
            ->query($sql, [$this->id]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $this->word = $row['word'];
        $this->hyphenatedWord = $row['result'];
    }

    public function create(): bool
    {
        try {
            $this->connectionHandle
                ->getHandle()
                ->beginTransaction();

            $sql = "INSERT INTO `{$this->tableName}` (`word`) VALUES (?)";
            $this->connectionHandle
                ->query($sql, [$this->word]);

            $this->connectionHandle
                ->query(
                    "INSERT INTO {$this->resultTable} (wordID) SELECT {$this->tableName}.id FROM {$this->tableName} WHERE word = ?",
                    [$this->word]);

            $this->connectionHandle
                ->query(
                    "UPDATE {$this->resultTable} 
                        INNER JOIN {$this->tableName} ON {$this->tableName}.id = {$this->resultTable}.wordID 
                        SET result = ? WHERE word = ?",
                    [$this->hyphenatedWord, $this->word]
                );

            foreach ($this->usedPatterns as $pattern) {
                if ($this->commitValidPattern($pattern))
                    continue;
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
        $sql = "UPDATE `{$this->tableName}` SET `word` = :word WHERE `id` = :id";
        $statement = $this->connectionHandle
            ->query($sql, [':word' => $this->word, ':id' => $this->id]);

        return $statement;
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM `{$this->tableName}` WHERE `id` = ?";
        $statement = $this->connectionHandle
            ->query($sql, [$this->id]);

        if ($statement)
            return true;
        return false;
    }

    private function commitValidPattern(string $pattern): bool
    {
        $sql = "insert into valid_patterns (wordID, patternID) 
                select w.id, p.id from {$this->tableName} w 
                inner join patterns p on p.pattern = ? and w.word = ?";
        $statement = $this->connectionHandle
            ->query($sql, [$pattern, $this->word]);
        if ($statement)
            return true;
        return false;
    }
}