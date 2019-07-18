<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

class Pattern extends Model
{
    private $tableName = "patterns";

    public $id;
    public $pattern;

    public function read(): object
    {
        $sql = "SELECT id, pattern FROM `{$this->tableName}` ORDER BY id DESC";
        $statement = $this->connectionHandle
            ->query($sql);
        return $statement;
    }

    public function readSingle(): void
    {
        $sql = "SELECT pattern FROM `{$this->tableName}` WHERE `id` = :id LIMIT 0, 1";
        $statement = $this->connectionHandle
            ->query($sql, [':id', $this->id]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $this->pattern = $row['pattern'];
    }

    public function create(): bool
    {
        $sql = "INSERT INTO `{$this->tableName}` (`pattern`) VALUES (?)";
        $statement = $this->connectionHandle
            ->query($sql, [$this->pattern]);

        return $statement;
    }

    public function update(): bool
    {
        $sql = "UPDATE `{$this->tableName}` SET `pattern` = :pattern WHERE `id` = :id";
        $statement = $this->connectionHandle
            ->query($sql, [':pattern' => $this->pattern, ':id' => $this->id]);

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