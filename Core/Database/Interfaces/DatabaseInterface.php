<?php
namespace Core\Database\Interfaces;

interface DatabaseInterface
{
    public function query(string $query, array $params = []);
}