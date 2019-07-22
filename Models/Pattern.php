<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

class Pattern extends Model
{
    private $tableName = "patterns";

    public $id;
    public $pattern;

    public function find(): bool
    {

        $statement = $this->connectionHandle
            ->getHandle()->prepare("SELECT id FROM {$this->tableName} WHERE id = {$this->id}");

        $statement->execute();

        if ($statement->rowCount() > 0)
            return true;
        return false;
    }

    public function findByPattern(): bool
    {
        $sql = "SELECT id FROM {$this->tableName} WHERE pattern = '{$this->pattern}' LIMIT 0, 1";
        $stmt = $this->connectionHandle
            ->getHandle()->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() > 0)
            return true;
        return false;
    }

    public function count(): int
    {
        $statement = $this->connectionHandle
            ->getHandle()
            ->query("SELECT id FROM {$this->tableName}");
        return $statement->rowCount();
    }

    public function read(): object
    {
        $sql = "SELECT id, pattern FROM {$this->tableName} ORDER BY id DESC";
        $statement = $this->connectionHandle
            ->getHandle()->prepare($sql);

        $statement->execute();

        return $statement;
    }

    public function readSingle(): void
    {
        $sql = "SELECT pattern FROM `{$this->tableName}` WHERE `id` = {$this->id} LIMIT 0, 1";
        $statement = $this->connectionHandle
            ->getHandle()->prepare($sql);
        $statement->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $this->pattern = $row['pattern'];
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