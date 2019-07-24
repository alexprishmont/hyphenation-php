<?php

namespace Core\Cache;

use Core\Application;
use Core\Cache\Interfaces\CacheInterface;
use Core\Exceptions\InvalidArgumentException;
use Core\Tools;

class FileCache implements CacheInterface
{
    private const PSR16_RESERVED = '/\{|\}|\(|\)|\/|\\\\|\@|\:/u';

    private $cachePath;
    private $dirMode;
    private $defaultTTL;
    private $fileMode;
    private static $instance;

    public function __construct($cachePath, $defaultTTL, $dirMode = 0775, $fileMode = 0664)
    {
        $this->defaultTTL = $defaultTTL;
        $this->dirMode = $dirMode;
        $this->fileMode = $fileMode;

        if (!file_exists($cachePath) && file_exists(dirname($cachePath)))
            $this->mkdir($cachePath);

        $path = realpath($cachePath);

        if ($path === false)
            throw new InvalidArgumentException('Cache path does not exist: ' . $cachePath);

        if (!is_writable($path . DIRECTORY_SEPARATOR))
            throw new InvalidArgumentException('Cache path is not writable: ' . $cachePath);

        $this->cachePath = $path;
    }

    public static function getInstanceOf()
    {
        if (!self::$instance) {
            self::$instance = new FileCache(Tools::getDefaultCachePath(Application::$settings),
                Tools::CACHE_DEFAULT_EXPIRATION,
                Tools::CACHE_DIR_MODE,
                Tools::CACHE_FILE_MODE);
        }
        return self::$instance;
    }

    public function get($key, $default = null)
    {
        $path = $this->getPath($key);
        $expiresAt = filemtime($path);

        if ($expiresAt === false)
            return $default;

        if ($this->getTime() >= $expiresAt) {
            unlink($path);
            return $default;
        }

        $data = file_get_contents($path);

        if ($data === false)
            return $default;

        if ($data === 'b:0;')
            return $default;

        $value = unserialize($data);

        if ($value === false)
            return $default;

        return $value;
    }

    public function getCacheExpirationTime($key)
    {
        $path = $this->getPath($key);
        return @filemtime($path);
    }

    public function set($key, $value, int $ttl = 0)
    {
        $path = $this->getPath($key);

        $dir = dirname($path);

        if (!file_exists($dir)) {
            $this->mkdir($dir);
        }

        $tempPath = $this->cachePath . DIRECTORY_SEPARATOR . uniqid('', true);

        if ($ttl === 0) {
            $expiresAt = $this->getTime() + $this->defaultTTL;
        } else {
            $expiresAt = $this->getTime() + $ttl;
        }

        if (!isset($expiresAt)) {
            throw new InvalidArgumentException('Invalid TTL: ' . print_r($ttl, true));
        }

        if (file_put_contents($tempPath, serialize($value)) == false) {
            return false;
        }

        if (chmod($tempPath, $this->fileMode) === false) {
            return false;
        }

        if (touch($tempPath, $expiresAt) && rename($tempPath, $path)) {
            return true;
        }

        unlink($tempPath);
        return false;
    }

    public function delete($key)
    {
        $this->validateKey($key);
        $path = $this->getPath($key);
        return !file_exists($path) || @unlink($path);
    }

    public function clear()
    {
        $success = true;

        $paths = $this->listPaths();

        foreach ($paths as $path) {
            if (!unlink($path)) {
                $success = false;
            }
        }

        return $success;
    }

    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('Keys must be either of type array or Traversable');
        }

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key) ?: $default;
        }

        return $values;
    }

    public function setMultiple($values, int $ttl = 0)
    {
        if (!is_array($values) && !$values instanceof \Traversable) {
            throw new InvalidArgumentException('Keys must be either of type array or Traversable');
        }

        $ok = true;

        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $key = (string)$key;
            }

            $this->validateKey($key);
            $ok = $this->set($key, $value, $ttl) && $ok;
        }

        return $ok;
    }

    public function deleteMultiple($keys)
    {
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('Keys must be either of type array or Traversable');
        }

        $ok = true;

        foreach ($keys as $key) {
            $this->validateKey($key);
            $ok = $ok && $this->delete($key);
        }

        return $ok;
    }

    public function has($key)
    {
        return $this->get($key, $this) !== $this;
    }

    private function getTime()
    {
        return time();
    }

    private function getPath($key)
    {
        $this->validateKey($key);

        $hash = hash("sha256", $key);

        return $this->cachePath
            . DIRECTORY_SEPARATOR
            . strtoupper($hash[0])
            . DIRECTORY_SEPARATOR
            . strtoupper($hash[1])
            . DIRECTORY_SEPARATOR
            . substr($hash, 2);
    }

    private function validateKey($key)
    {
        if (!is_string($key)) {
            $type = is_object($key) ? get_class($key) : gettype($key);
            throw new InvalidArgumentException('Invalid key type: ' . $type . ' given');
        }

        if ($key === "") {
            throw new InvalidArgumentException('Invalid key: empty string given');
        }

        if ($key === null) {
            throw new InvalidArgumentException('Invalid key: null given');
        }

        if (preg_match(self::PSR16_RESERVED, $key, $match) === 1) {
            throw new InvalidArgumentException('Invalid character in key: ' . $match[0]);
        }
    }

    private function listPaths()
    {
        $iterator = new \RecursiveDirectoryIterator(
            $this->cachePath,
            \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
        );

        $iterator = new \RecursiveIteratorIterator($iterator);

        foreach ($iterator as $path) {
            if (is_dir($path)) {
                continue;
            }
            yield $path;
        }
    }

    private function mkdir($path)
    {
        $parent_path = dirname($path);

        if (!file_exists($parent_path)) {
            $this->mkdir($parent_path);
        }

        mkdir($path);
        chmod($path, $this->dirMode);
    }
}