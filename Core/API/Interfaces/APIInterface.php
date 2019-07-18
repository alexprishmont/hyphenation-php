<?php

namespace Core\API\Interfaces;

interface APIInterface
{
    public function read();

    public function readSingle(int $id);

    public function create(array $data): bool;

    public function update(array $data): bool;

    public function delete(int $id): bool;
}