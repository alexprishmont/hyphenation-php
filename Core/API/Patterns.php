<?php
declare(strict_types=1);

namespace Core\API;

use Core\API\Interfaces\APIInterface;
use Models\Pattern;

class Patterns implements APIInterface
{
    private $pattern;

    public function __construct(Pattern $pattern)
    {
        $this->pattern = $pattern;
    }

    public function find(int $id) {
        $this->pattern->id = $id;
        return $this->pattern->find();
    }

    public function read()
    {
        $statement = $this->pattern->read();

        if ($statement->rowCount() === 0) {
            return false;
        }

        $patternArray = [];
        $patternArray['data'] = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            extract($row);
            $patternItem = [
                "id" => $id,
                "pattern" => $pattern
            ];
            array_push($patternArray['data'], $patternItem);
        }
        return json_encode($patternArray);
    }

    public function create(array $data): bool
    {
        $this->pattern->pattern = $data['pattern'];
        if ($this->pattern->create())
            return true;
        else
            return false;
    }

    public function readSingle(int $id)
    {
        $this->pattern->id = $id;
        $this->pattern->readSingle();

        if ($this->pattern->pattern === null)
            return false;

        return json_encode([
            "id" => $this->pattern->id,
            "pattern" => $this->pattern->pattern
        ]);
    }

    public function update(array $data): bool
    {
        $this->pattern->id = $data['id'];
        $this->pattern->pattern = $data['pattern'];

        if ($this->pattern->update())
            return true;

        return false;
    }

    public function delete(int $id): bool
    {
        $this->pattern->id = $id;
        return $this->pattern->delete();
    }
}