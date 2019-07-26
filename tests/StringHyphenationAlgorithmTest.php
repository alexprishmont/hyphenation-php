<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class StringHyphenationAlgorithmTest extends TestCase
{
    private $algorithm;

    protected function setUp(): void
    {
        $config = $this->createMock(\Core\Config::class);
        $config->method('get')
            ->willReturn(
                json_decode(
                    file_get_contents(dirname(__FILE__, 2) . '/config/config.json'),
                    true
                )
            );
        \Core\Application::$settings = $config->get('config');

        $word = $this->createMock(\Models\Word::class);
        $pattern = $this->createMock(\Models\Pattern::class);
        $scan = $this->createMock(\Core\Scans\Scan::class);

        $proxy = new \Algorithms\Proxy($word, $pattern, $scan);

        $this->algorithm = new \Algorithms\StringHyphenation($proxy);
    }

    protected function tearDown(): void
    {
        $this->algorithm = null;
    }

    public function addDataProvider()
    {
        return [
            ['New config file is proper', 'new con-fig file is prop-er'],
            [
                'Unit testing is the best way to test my code on failure',
                'Unit Test-ing is the best way to test my code on fail-ure'
            ]
        ];
    }

    /**
     * @dataProvider addDataProvider
     */
    public function testStringHyphenation($data, $expected)
    {
        $result = $this->algorithm->hyphenate($data);
        $this->assertEquals($result, $expected);
    }
}