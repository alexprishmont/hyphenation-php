<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    private $config;
    private $settings = [];
    private const DEFAULT_SETTINGS = [
        'PATTENRS_SOURCE' => 'tex-hyphenation-patterns.txt',
        'INPUT_SRC' => '/Data',
        'OUTPUT_SRC' => '/Output',
        'CACHE_OUTPUT_SRC' => '/Cache'
    ];
    protected function setUp(): void
    {
        $this->config = new \NXT\Core\Config;
    }

    protected function tearDown(): void
    {
        $this->config->set('config', self::DEFAULT_SETTINGS);
        $this->config = null;
    }

    public function testConfigReading()
    {
        $result = $this->config->get('config');
        $this->settings = json_decode(
            file_get_contents(dirname(__FILE__, 2) . '/config/config.json'),
            true
        );

        $this->assertEquals($this->settings, $result);
    }

    public function testConfigWriting()
    {
        $newTestcase = $this->settings;
        $newTestcase['PATTERNS_SOURCE'] = 'test';
        $this->config->set('config', $newTestcase);

        $testCase = $this->config->get('config');

        $this->assertEquals($newTestcase, $testCase);
    }
}