<?php
declare(strict_types=1);

namespace Core\Database\Interfaces;


interface BuilderInterface
{
    public static function getInstanceOf();

    public function table(string $table);

    public function select(array $columns);

    public function where(array $columns);

    public function order(string $column, string $type);

    public function insert(array $values);
}
