<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

class Pattern extends Model
{
    private $tableName = "patterns";

    private $id;
    private $pattern;

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

    public function find(): bool
    {
        if ($this->pattern === null && $this->id !== null) {
            $statement = $this->connectionHandle
                ->getHandle()->prepare("SELECT id FROM {$this->tableName} WHERE id = {$this->id}");

            $statement->execute();

            if ($statement->rowCount() > 0) {
                return true;
            }
            return false;
        }

        $statement = $this->connectionHandle
            ->getHandle()
            ->prepare("SELECT id FROM {$this->tableName} WHERE pattern = '{$this->pattern}'");
        $statement->execute();
        if ($statement->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function count(): int
    {
        $statement = $this->connectionHandle
            ->getHandle()
            ->query("SELECT id FROM {$this->tableName}");
        return $statement->rowCount();
    }

    public function read()
    {
        if ($this->id === null) {
            $sql = "SELECT id, pattern FROM {$this->tableName} ORDER BY id DESC";
            $statement = $this->connectionHandle
                ->getHandle()->prepare($sql);

            $statement->execute();
            return $statement;
        }

        $sql = "SELECT pattern FROM `{$this->tableName}` WHERE `id` = {$this->id} LIMIT 0, 1";
        $statement = $this->connectionHandle
            ->getHandle()->prepare($sql);
        $statement->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $this->pattern = $row['pattern'];
        return $this->pattern;
    }

    public function create(): bool
    {
        $sql = "INSERT INTO `{$this->tableName}` (`pattern`) VALUES ('{$this->pattern}')";
        $statement = $this->connectionHandle
            ->getHandle()->prepare($sql);
        $statement->execute();

        if ($statement)
            return true;
        return false;
    }

    public function update(): bool
    {
        $sql = "UPDATE `{$this->tableName}` SET `pattern` = '{$this->pattern}' WHERE `id` = {$this->id}";
        $statement = $this->connectionHandle
            ->getHandle()->prepare($sql);
        $statement->execute();

        if ($statement)
            return true;
        return false;
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM `{$this->tableName}` WHERE `id` = {$this->id}";
        $statement = $this->connectionHandle
            ->getHandle()->prepare($sql);
        $statement->execute();

        if ($statement)
            return true;
        return false;
    }
}