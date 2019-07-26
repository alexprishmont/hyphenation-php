<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use NXT\Algorithms\Hyphenation;

class HyphenationAlgorithmTest extends TestCase
{
    private $hyphenation;

    protected function setUp(): void
    {
        $patterns = file_get_contents(
            dirname(__FILE__, 2) . '/app/Data/tex-hyphenation-patterns.txt'
        );
        $patterns = preg_split('/\s+/', $patterns);
        $this->hyphenation = new Hyphenation($patterns);
    }

    protected function tearDown(): void
    {
        $this->hyphenation = null;
    }

    public function addDataProviderForWords()
    {
        return [
            ['mistranslate', 'mis-trans-late'],
            ['forever', 'for-ev-er'],
            ['forest', 'for-est'],
            ['going', 'go-ing'],
            ['walking', 'walk-ing'],
            ['feature', 'fea-ture'],
            ['better', 'bet-ter'],
            ['beginner', 'be-gin-ner']
        ];
    }

    /**
     * @dataProvider addDataProviderForWords
     */
    public function testHyphenationAlgorithm($a, $expected)
    {
        $result = $this->hyphenation->hyphenate($a);
        $this->assertEquals($expected, $result);
    }
}