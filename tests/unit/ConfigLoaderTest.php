<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

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
        $this->config = new \NXT\Core\Config();
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
}