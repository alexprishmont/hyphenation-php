<?php
declare(strict_types=1);

namespace Core\API;

use Core\API\Interfaces\APIInterface;
use Models\Word;

class Words implements APIInterface
{
    private $word;

    public function __construct(Word $word)
    {
        $this->word = $word;
    }

    public function find(int $id)
    {
        $this->word->id = $id;
        return $this->word->find();
    }

    public function read()
    {
        $statement = $this->word->read();

        if ($statement->rowCount() <= 0)
            return false;

        $wordArray = [];
        $wordArray['data'] = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            extract($row);
            $wordItem = [
                "id" => $id,
                "word" => $word,
                "hyphenated" => $result
            ];
            array_push($wordArray['data'], $wordItem);
        }
        return json_encode($wordArray);
    }

    public function readSingle(int $id)
    {
        $this->word->id = $id;
        $this->word->readSingle();

        if ($this->word->word === null || $this->word->hyphenatedWord === null)
            return false;

        return json_encode([
            'id' => $id,
            'word' => $this->word->word,
            'hyphenated' => $this->word->hyphenatedWord
        ]);
    }

    public function create(array $data): bool
    {
        $this->word->word = $data['word'];
        $this->word->hyphenatedWord = $data['hyphenated'];
        return $this->word
            ->create();
    }

    public function update(array $data): bool
    {
        $this->word->word = $data['word'];
        $this->word->hyphenatedWord = $data['hyphenated'];
        $this->word->id = $data['id'];
        return $this->word
            ->update();
    }

    public function delete(int $id): bool
    {
        $this->word->id = $id;
        return $this->word->delete();
    }
}