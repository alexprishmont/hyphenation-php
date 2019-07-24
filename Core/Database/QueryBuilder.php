<?php
declare(strict_types=1);

namespace Core\Database;

use Core\Database\Interfaces\BuilderInterface;

class QueryBuilder implements BuilderInterface
{
    private $connection;
    private static $instance;

    private $table;
    private $queryCondition;

    public function __construct()
    {
        $this->connection = Singleton::getInstanceOf();
    }

    public static function getInstanceOf()
    {
        if (!self::$instance) {
            self::$instance = new QueryBuilder();
        }
        return self::$instance;
    }

    public function table(string $table)
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    public function select(array $columns)
    {
        $this->queryCondition .= 'SELECT ';
        foreach ($columns as $column) {
            if ($this->checkKeyPosition($columns, $column)) {
                $this->queryCondition .= $column . ', ';
                continue;
            }
            $this->queryCondition .= $column . ' ';
        }
        return $this;
    }

    public function from(string $from = null)
    {
        if ($from !== null) {
            $this->queryCondition .= 'FROM ' . $from;
        } else {
            $this->queryCondition .= 'FROM ' . $this->table;
        }
        return $this;
    }

    public function where(array $columns)
    {
        if ($this->queryCondition === null) {
            throw new \Exception('Cannot use `where` as theres no full built query.');
        }
        $this->queryCondition .= ' WHERE ';
        foreach ($columns as $key => $value) {
            $keysArray = array_keys($columns);
            if ($this->checkKeyPosition($keysArray, $key)) {
                $this->queryCondition .= $key . ' = ' . $value . ' AND ';
                continue;
            }
            $this->queryCondition .= "{$key} = '{$value}'";
        }
        return $this;
    }

    public function order(string $column, string $type)
    {
        if ($this->queryCondition === null) {
            throw new \Exception('Cannot use `order` as theres no full built query.');
        }
        $this->queryCondition .= " ORDER BY {$column} " . strtoupper($type);
        return $this;
    }

    public function insert(array $values)
    {
        $this->queryCondition .= ' INSERT INTO ' . $this->table . ' (';

        foreach ($values as $value) {
            if ($this->checkKeyPosition($values, $value)) {
                $this->queryCondition .= $value . ', ';
                continue;
            }
            $this->queryCondition .= $value . ')';
        }
        return $this;
    }

    public function values(array $values)
    {
        $this->queryCondition .= ' VALUES (';
        foreach ($values as $value) {
            if ($this->checkKeyPosition($values, $value)) {
                $this->queryCondition .= "'{$value}', ";
                continue;
            }
            $this->queryCondition .= "'{$value}')";
        }
        return $this;
    }

    public function update()
    {
        $this->queryCondition .= 'UPDATE ' . $this->table;
        return $this;
    }

    public function set(array $values)
    {
        $this->queryCondition .= ' SET ';
        foreach ($values as $key => $value) {
            $keysArray = array_keys($values);
            if ($this->checkKeyPosition($keysArray, $key)) {
                $this->queryCondition .= "{$key} = '{$value}', ";
                continue;
            }
            $this->queryCondition .= "{$key} = '{$value}'";
        }
        return $this;
    }

    public function limit(array $limits)
    {
        $this->queryCondition .= ' LIMIT ';
        foreach ($limits as $limit) {
            if ($this->checkKeyPosition($limits, $limit)) {
                $this->queryCondition .= $limit . ', ';
                continue;
            }
            $this->queryCondition .= $limit;
        }
        return $this;
    }

    public function delete()
    {
        $this->queryCondition .= 'DELETE FROM ' . $this->table;
        return $this;
    }

    public function inner()
    {
        $this->queryCondition .= ' INNER';
        return $this;
    }

    public function join(string $table)
    {
        $this->queryCondition .= ' JOIN ' . $table;
        return $this;
    }

    public function on(array $params)
    {
        $this->queryCondition .= 'ON ';
        foreach ($params as $key => $value) {
            $keyArray = array_keys($params);
            if ($this->checkKeyPosition($keyArray, $key)) {
                $this->queryCondition .= $key . ' = ' . $value . ' AND ';
                continue;
            }
            $this->queryCondition .= $key . ' = ' . $value;
        }
        return $this;
    }

    public function execute()
    {
        $handle = $this->connection->getHandle();
        $statement = $handle->prepare($this->queryCondition);
        $statement->execute();
        return $statement;
    }

    private function reset()
    {
        $this->table = null;
        $this->queryCondition = null;
    }


    private function checkKeyPosition(array $values, $value)
    {
        $key = array_search($value, $values);
        if ($key !== sizeof($values) - 1) {
            return true;
        }
        return false;
    }
}