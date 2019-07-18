<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

class Word extends Model
{
    private $tableName = "words";
    private $resultTable = "results";

    public $id;
    public $word;
    public $hyphenatedWord;

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
        $sql = "SELECT word, result FROM {$this->tableName}
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID
                ORDER BY {$this->tableName}.id DESC";
        $statement = $this->connectionHandle
            ->query($sql);
        return $statement;
    }

    public function readSingle(): void
    {
        $sql = "SELECT word, result FROM {$this->tableName}
                INNER JOIN {$this->resultTable} ON {$this->tableName}.id = {$this->resultTable}.wordID
                WHERE id = :id LIMIT 0, 1";
        $statement = $this->connectionHandle
            ->query($sql, [':id', $this->id]);

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
                    "INSERT INTO {$this->resultTable} (wordID) SELECT words.id FROM {$this->tableName} WHERE word = ?",
                    [$this->word]);

            $this->connectionHandle
                ->query(
                    "UPDATE {$this->resultTable} 
                        INNER JOIN {$this->tableName} ON words.id = results.wordID 
                        SET result = ? WHERE word = ?",
                    [$this->hyphenatedWord, $this->word]
                );

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

        return $statement;
    }
}