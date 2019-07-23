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

    public function values(array $values);

    public function update();

    public function set(array $values);

    public function limit(array $limits);

    public function delete();

    public function inner();

    public function join(string $table);

    public function on(array $params);

    public function execute();
}
