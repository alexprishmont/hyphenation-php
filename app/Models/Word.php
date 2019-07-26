<?php
declare(strict_types=1);

namespace NXT\Models;

use NXT\Core\Model;

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
        $stmt = $this->builder
            ->table($this->tableName)
            ->select(['id'])
            ->from($this->tableName)
            ->execute();
        return $stmt->rowCount();
    }

    public function find($input): bool
    {
        if (is_int($input)) {
            $this->id = $input;
            $statement = $this->findById();
        } else if (is_string($input)) {
            $this->word = $input;
            $statement = $this->findByWord();
        }

        if ($statement->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getAll(): array
    {
        $words = $this->read();
        $resultArray = [];
        $resultArray['data'] = [];
        while ($data = $words->fetch(\PDO::FETCH_ASSOC)) {
            array_push($resultArray['data'],
                [
                    'id' => $data['id'],
                    'original_word' => $data['word'],
                    'hyphenated' => $data['result']
                ]
            );
        }
        return $resultArray;
    }

    public function read()
    {
        if ($this->id !== null || $this->word !== null) {
            return $this->getByWordOrID();
        }
        $statement = $this->builder
            ->table($this->tableName)
            ->select(['*'])
            ->from()
            ->inner()
            ->join($this->resultTable)
            ->on(['results.wordID' => 'words.id'])
            ->order('words.id', 'asc')
            ->execute();
        return $statement;
    }

    public function create(): bool
    {
        try {
            $this->connectionHandle
                ->getHandle()
                ->beginTransaction();

            $this->builder
                ->table($this->tableName)
                ->insert(['word'])
                ->values([$this->word])
                ->execute();


            $selectID = $this->tableName . '.id';
            $this->builder
                ->table($this->resultTable)
                ->insert(['wordID'])
                ->select([$selectID])
                ->from($this->tableName)
                ->where(['word' => $this->word])
                ->execute();

            $this->builder
                ->table($this->resultTable)
                ->update()
                ->inner()
                ->join($this->tableName)
                ->on(['words.id' => 'results.wordID'])
                ->set(['result' => $this->hyphenatedWord])
                ->where(['word' => $this->word])
                ->execute();


            foreach ($this->usedPatterns as $pattern) {
                if ($this->commitValidPattern($pattern)) {
                    continue;
                }
            }

            $this->connectionHandle
                ->getHandle()
                ->commit();
            return true;
        } catch (\PDOException $e) {
            $this->connectionHandle
                ->getHandle()
                ->rollBack();
            return false;
        }
    }

    public function delete(): bool
    {
        $statement = $this->builder
            ->table($this->tableName)
            ->delete()
            ->where(['id' => $this->id])
            ->execute();

        if ($statement)
            return true;
        return false;
    }

    private function commitValidPattern(string $pattern): bool
    {
        $statement = $this->builder
            ->table('valid_patterns')
            ->insert(['wordID', 'patternID'])
            ->select(['words.id', 'patterns.id'])
            ->from($this->tableName)
            ->inner()
            ->join('patterns')
            ->on([
                'patterns.pattern' => "'$pattern'",
                'words.word' => "'$this->word'"
            ])
            ->execute();

        if ($statement)
            return true;
        return false;
    }

    private function getByWordOrID()
    {
        $statement = $this->getStatement();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $this->word = $row['word'];
        $this->hyphenatedWord = $row['result'];
        return [
            "word" => $this->word,
            "result" => $this->hyphenatedWord
        ];
    }

    private function getStatement()
    {
        if ($this->word !== null) {
            $stmt = $this->builder
                ->table($this->tableName)
                ->select(['*'])
                ->from($this->tableName)
                ->inner()
                ->join($this->resultTable)
                ->on(['results.wordID' => 'words.id'])
                ->where(['words.word' => $this->word])
                ->limit(['0', '1'])
                ->execute();
            return $stmt;
        } else if ($this->id !== null) {
            $stmt = $this->builder
                ->table($this->tableName)
                ->select(['*'])
                ->from($this->tableName)
                ->inner()
                ->join($this->resultTable)
                ->on(['results.wordID' => 'words.id'])
                ->where(['words.id' => $this->id])
                ->limit(['0', '1'])
                ->execute();
            return $stmt;
        }
    }

    private function findById()
    {
        $statement = $this->builder
            ->table($this->tableName)
            ->select(['id'])
            ->from($this->tableName)
            ->where(['id' => $this->id])
            ->limit(['0', '1'])
            ->execute();
        return $statement;
    }

    private function findByWord()
    {
        $statement = $this->builder
            ->table($this->tableName)
            ->select(['id'])
            ->from($this->tableName)
            ->where(['word' => $this->word])
            ->limit(['0', '1'])
            ->execute();
        return $statement;
    }

}
