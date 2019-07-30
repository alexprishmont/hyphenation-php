<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use NXT\Algorithms\{Proxy, StringHyphenation};

class StringHyphenationAlgorithmTest extends TestCase
{
    private $hyphenation;

    protected function setUp(): void
    {
        $proxy = $this->createMock(Proxy::class);

        $map = [
            ['mistranslate', 'mis-trans-late'],
            ['is', 'is'],
            ['successfully', 'suc-cess-ful-ly'],
            ['proceeded', 'pro-ceed-ed']
        ];

        $proxy->expects($this->any())
            ->method('hyphenate')
            ->willReturnMap($map);

        $this->hyphenation = new StringHyphenation($proxy);
    }

    protected function tearDown(): void
    {
        $this->hyphenation = null;
    }

    public function testStringHyphenation(): void
    {


        $result = $this->hyphenation->hyphenate('mistranslate is successfully proceeded');
        $this->assertEquals('mis-trans-late is suc-cess-ful-ly pro-ceed-ed', $result);
    }

}