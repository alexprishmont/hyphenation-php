<?php
declare(strict_types=1);

namespace NXT\Core\Database;


use NXT\Core\Cache\FileCache;
use NXT\Core\Database\Interfaces\ImportInterface;
use PDO;

class Import implements ImportInterface
{

    private $patterns = [];
    private $connection;
    private $cache;

    public function __construct()
    {
        $this->connection = Singleton::getInstanceOf();
        $this->cache = FileCache::getInstanceOf();
    }

    public function patterns(array $patterns)
    {
        $this->patterns = $patterns;
        return $this;
    }

    public function importPatterns()
    {
        try {
            $this->connection
                ->getHandle()
                ->beginTransaction();

            $this->clearTables();

            $statement = $this->connection
                ->getHandle()
                ->prepare("insert into `patterns` (`pattern`) values (:pattern)");
            foreach ($this->patterns as $pattern) {
                $pattern = trim(preg_replace('/\s\s+/', ' ', $pattern));
                $statement->bindParam(':pattern', $pattern, PDO::PARAM_STR, 250);
                $statement->execute([$pattern]);
            }

            if ($this->cache->has('patterns')) {
                $this->cache->delete('patterns');
            }

            $this->cache->set('patterns', $this->patterns);

            $this->connection
                ->getHandle()
                ->commit();
        } catch (\PDOException $e) {
            $this->connection
                ->getHandle()
                ->rollBack();
        }
    }

    private function clearTables()
    {
        $this->connection
            ->getHandle()
            ->query('SET FOREIGN_KEY_CHECKS=0');

        $this->connection
            ->getHandle()
            ->query('TRUNCATE TABLE `valid_patterns`');

        $this->connection
            ->getHandle()
            ->query('TRUNCATE TABLE `words`');

        $this->connection
            ->getHandle()
            ->query('TRUNCATE TABLE `results`');

        $this->connection
            ->getHandle()
            ->query('TRUNCATE TABLE `patterns`');

        $this->connection
            ->getHandle()
            ->query('SET FOREIGN_KEY_CHECKS=1');

    }

}