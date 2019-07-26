<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    private $config;
    private $settings = [];

    protected function setUp(): void
    {
        $this->config = new \NXT\Core\Config;
    }

    protected function tearDown(): void
    {
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