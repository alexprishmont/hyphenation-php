<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use NXT\Core\Config;
use NXT\Core\Exceptions\InvalidFlagException;

class ConfigLoaderTest extends TestCase
{
    private $config;
    private $settings = [];
    private const DEFAULT_SETTINGS = [
        'PATTENRS_SOURCE' => 'tex-hyphenation-patterns.txt',
        'INPUT_SRC' => '/resources',
        'OUTPUT_SRC' => '/output',
        'CACHE_OUTPUT_SRC' => '/cache'
    ];

    protected function setUp(): void
    {
        $this->config = new Config();
    }

    protected function tearDown(): void
    {
        $this->config->set('config', self::DEFAULT_SETTINGS);
        $this->config = null;
    }

    public function testConfigReading(): void
    {
        $result = $this->config->get('config');
        $this->assertEquals(self::DEFAULT_SETTINGS, $result);
    }

    public function testConfigWriting(): void
    {
        $newTestcase = $this->settings;
        $newTestcase['PATTERNS_SOURCE'] = 'test';
        $this->config->set('config', $newTestcase);

        $testCase = $this->config->get('config');

        $this->assertEquals($newTestcase, $testCase);
    }

    public function testTryReadEmptyConfig(): void
    {
        $this->expectException(InvalidFlagException::class);

        $newTestcase = [];
        $this->config->set('config', $newTestcase);
        $this->config->get('config');
    }

    public function testTryReadNotExistingConfig(): void
    {
        $this->expectException(InvalidFlagException::class);

        $this->config->get('notexistingConfig');
    }

    public function testTrySetNotExistingConfig(): void
    {
        $this->expectException(InvalidFlagException::class);
        $this->config->set('notexisting', []);
    }

    public function testTrySetEmptyConfig(): void
    {
        $this->expectException(InvalidFlagException::class);
        $this->config->set('config', []);
    }
}