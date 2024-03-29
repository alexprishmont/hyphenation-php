<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use NXT\Algorithms\Hyphenation;

class HyphenationAlgorithmTest extends TestCase
{
    private $hyphenation;

    private const PATTERNS = [
        '.mis1',
        '1tra',
        '2n1s2',
        '4te.',
        'a2n',
        'm2is',
        'n2sl',
        's1l2',
        's3lat',
        'st4r',
        '1fo',
        'ev1er',
        'fo2r',
        'r5ev5er.',
        'rev2'
    ];

    protected function setUp(): void
    {
        $this->hyphenation = new Hyphenation(self::PATTERNS);
    }

    protected function tearDown(): void
    {
        $this->hyphenation = null;
    }

    public function addDataProviderForWords()
    {
        return [
            ['mistranslate', 'mis-trans-late'],
            ['forever', 'for-ev-er']
        ];
    }

    public function addDataProviderForValidPatterns()
    {
        return [
            [
                'mistranslate',
                [
                    '.mis1',
                    '1tra',
                    '2n1s2',
                    '4te.',
                    'a2n',
                    'm2is',
                    'n2sl',
                    's1l2',
                    's3lat',
                    'st4r'
                ]
            ]
        ];
    }

    /**
     * @dataProvider addDataProviderForWords
     * @param $word
     * @param $expected
     */
    public function testHyphenationAlgorithm($word, $expected)
    {
        $result = $this->hyphenation->hyphenate($word);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider addDataProviderForValidPatterns
     * @param $word
     * @param $expected
     */
    public function testGetValidPatternsForWord($word, $expected)
    {
        $result = $this->hyphenation->getValidPatternsForWord($word);
        $this->assertEquals($expected, $result);
    }
}