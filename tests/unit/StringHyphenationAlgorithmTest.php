<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class StringHyphenationAlgorithmTest extends TestCase
{
    private $hyphenation;

    protected function setUp(): void
    {
        $proxy = $this->createMock(\NXT\Algorithms\Proxy::class);
        $proxy->expects($this->once())
            ->method('hyphenate')
            ->with('mistranslate')
            ->willReturn('mis-trans-late');

        // willReturnMap([]);

        $this->hyphenation = new \NXT\Algorithms\StringHyphenation($proxy);
    }

    protected function tearDown(): void
    {
        $this->hyphenation = null;
    }

    public function testStringHyphenation(): void
    {
        $result = $this->hyphenation->hyphenate('mistranslate');
        $this->assertEquals('mis-trans-late', $result);
    }

}