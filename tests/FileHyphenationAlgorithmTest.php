<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FileHyphenationAlgorithmTest extends TestCase
{
    private $hyphenation;
    private const RESULT_FOR_TEST_CASE = 'Mis-trans-late is a fea-ture.';

    protected function setUp(): void
    {
        $string = $this->createMock(\NXT\Algorithms\StringHyphenation::class);
        $string->expects($this->once())
            ->method('hyphenate')
            ->with(file_get_contents(dirname(__FILE__) . '/fileTest.txt'))
            ->willReturn(self::RESULT_FOR_TEST_CASE);

        $this->hyphenation = new \NXT\Algorithms\FileHyphenation($string);
    }

    protected function tearDown(): void
    {
        $this->hyphenation = null;
    }

    public function testFileHyphenation()
    {
        $result = $this->hyphenation->hyphenate('../tests/fileTest.txt');
        $this->assertEquals(self::RESULT_FOR_TEST_CASE, $result);
    }
}