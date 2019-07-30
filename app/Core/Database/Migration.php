<?php
declare(strict_types=1);

namespace NXT\Core\Database;

use NXT\Core\Database\Interfaces\MigrationInterface;
use NXT\Core\Log\Logger;
use NXT\Core\Log\LogLevel;

class Migration implements MigrationInterface
{

    private $db;
    private $logger;

    public function __construct(Logger $log)
    {
        $this->db = Singleton::getInstanceOf();
        $this->logger = $log;
    }

    public function migrate(string $migrationName): void
    {
        $path = $this->getPath() . $migrationName . '.sql';
        if (!file_exists($path)) {
            throw new \Exception('Such migration does not exist.');
        }

        $sql = file_get_contents($path);
        try {
            $this->db
                ->getHandle()->exec($sql);
            $this->logger
                ->log(LogLevel::SUCCESS, 'Migration [' . $migrationName . ']');
        } catch (\Exception $e) {
            $this->logger
                ->log(LogLevel::ERROR, 'Migration [' . $migrationName . '] failure.');
        }
    }

    private function getPath(): string
    {
        return dirname(__FILE__, 3) . '/Migrations/';
    }

}