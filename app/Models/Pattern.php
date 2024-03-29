<?php
declare(strict_types=1);

namespace NXT\Models;

use NXT\Core\Model;

class Pattern extends Model
{
    private $tableName = "patterns";

    private $id;
    private $pattern;

    private const ITEM_PER_PAGE = 10;

    public function id(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function pattern(string $pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function getByPage(int $page): array
    {
        $itemPerPage = self::ITEM_PER_PAGE;
        $startFrom = ($page - 1) * $itemPerPage;

        $statement = $this->builder
            ->table($this->tableName)
            ->select(['*'])
            ->from($this->tableName)
            ->order('id', 'asc')
            ->limit([$startFrom, $itemPerPage])
            ->execute();

        $row = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getPageCount(): int
    {
        $count = $this->count();
        return (int) ceil($count / self::ITEM_PER_PAGE);
    }

    public function find($input): bool
    {
        if (is_int($input)) {
            $this->id = $input;
            $statement = $this->builder
                ->table($this->tableName)
                ->select(["id"])
                ->from($this->tableName)
                ->where(["id" => $this->id])
                ->execute();
        } else if (is_string($input)) {
            $this->pattern = $input;
            $statement = $this->builder
                ->table($this->tableName)
                ->select(['id'])
                ->from($this->tableName)
                ->where(['pattern' => $this->pattern])
                ->execute();
        }

        if ($statement->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getAll(): array
    {
        $patterns = $this->read();
        $resultArray = [];
        $resultArray['data'] = [];
        while ($data = $patterns->fetch(\PDO::FETCH_ASSOC)) {
            array_push($resultArray['data'],
                [
                    'id' => $data['id'],
                    'pattern' => $data['pattern']
                ]
            );
        }
        return $resultArray;
    }

    public function count(): int
    {
        $statement = $this->builder
            ->table($this->tableName)
            ->select(['id'])
            ->from($this->tableName)
            ->execute();
        return $statement->rowCount();
    }

    public function read()
    {
        if ($this->id === null) {
            $statement = $this->builder
                ->table($this->tableName)
                ->select(['id', 'pattern'])
                ->from($this->tableName)
                ->order('id', 'desc')
                ->execute();
            return $statement;
        }

        $statement = $this->builder
            ->table($this->tableName)
            ->select(['pattern'])
            ->from($this->tableName)
            ->where(['id' => $this->id])
            ->limit(['0', '1'])
            ->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $this->pattern = $row['pattern'];
        return $this->pattern;
    }

    public function create(): bool
    {
        $statement = $this->builder
            ->table($this->tableName)
            ->insert(['pattern'])
            ->values([$this->pattern])
            ->execute();

        if ($statement)
            return true;
        return false;
    }

    public function update(): bool
    {
        $statement = $this->builder
            ->table($this->tableName)
            ->update()
            ->set(['pattern' => $this->pattern])
            ->where(['id' => $this->id])
            ->execute();

        if ($statement)
            return true;
        return false;
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
}