<?php
declare(strict_types=1);

namespace Core\Database;

use Core\Application;
use Core\Cache\FileCache;
use Core\Database\Interfaces\DatabaseInterface;
use Core\Log\Logger;
use Core\Log\LogLevel;
use Core\Scans\Scan;
use Core\Tools;
use \PDO;

class Connection implements DatabaseInterface
{
    private $handle;

    private $logger;
    private $cache;
    private $scan;

    public function __construct(Logger $log, FileCache $cache, Scan $scan)
    {
        $this->logger = $log;
        $this->cache = $cache;
        $this->scan = $scan;

        $this->cache->setup(Tools::getDefaultCachePath(Application::$settings),
            Tools::CACHE_DEFAULT_EXPIRATION,
            Tools::CACHE_DIR_MODE,
            Tools::CACHE_FILE_MODE
        );

        $dsn = "mysql:host=" . DatabaseSettings::get()['host'] .
            ";dbname=" . DatabaseSettings::get()['database'] .
            ";charset=" . DatabaseSettings::get()['charset'];

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->handle = new PDO($dsn,
            DatabaseSettings::get()['user'],
            DatabaseSettings::get()['password'],
            $options);
    }

    public function getHandle(): object
    {
        return $this->handle;
    }

    public function query(string $stmt, array $params = [])
    {
        $statement = $this->handle->prepare($stmt);
        $statement->execute($params);
        return $statement;
    }

    public function getPatterns(): array
    {
        return $this->loadPatternsToCache();
    }

    private function loadPatternsToCache(): array
    {
        if ($this->cache->has("patterns")) {
            return $this->cache->get("patterns");
        }

        $fetch = $this->handle->query("select pattern from `patterns`");
        $fetch = $fetch->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($fetch as $data) {
            $result[] = $data['pattern'];
        }
        $this->cache->set("patterns", $result);
        return $result;
    }

    public function importPatterns(): void
    {
        $patterns = $this->scan->readDataFromFile(Application::$settings['PATTERNS_SOURCE']);
        try {
            $this->handle->beginTransaction();

            $statement = $this->handle->prepare("replace into `patterns` (`pattern`) values (?)");
            foreach ($patterns as $pattern) {
                $statement->execute([$pattern]);
            }

            $this->handle->commit();
            $this->logger->log(LogLevel::SUCCESS, "New patterns imported.");
        } catch (\Exception $e) {
            $this->handle->rollBack();
            $this->logger
                ->log(LogLevel::ERROR,
                    $e->getMessage());
        }
    }
}