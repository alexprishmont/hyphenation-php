<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use NXT\Core\Exceptions\InvalidFlagException;
use NXT\Algorithms\{FileHyphenation, StringHyphenation};

class FileHyphenationAlgorithmTest extends TestCase
{
    private $hyphenation;
    private const RESULT_FOR_TEST_CASE = 'Mis-trans-late is a fea-ture.';
    private const TEST_CASE = 'Mistranslate is a feature.';
    private const TEMP_FILE = 'test.txt';

    protected function setUp(): void
    {
        $string = $this->createMock(StringHyphenation::class);
        // move to testFileHyphenation
        $string->expects($this->any())
            ->method('hyphenate')
            ->with(self::TEST_CASE)
            ->willReturn(self::RESULT_FOR_TEST_CASE);

        $this->hyphenation = new FileHyphenation($string);
        $this->createTempFile();
        $this->writeTempFile();
    }

    protected function tearDown(): void
    {
        $this->hyphenation = null;
        $this->deleteTempFile();
    }

    public function testFileHyphenation(): void
    {
        $result = $this->hyphenation->hyphenate('../tests/' . self::TEMP_FILE);
        $this->assertEquals(self::RESULT_FOR_TEST_CASE, $result);
    }

    public function testWrongFilePath(): void
    {
        $this->expectException(InvalidFlagException::class);
        $this->hyphenation->hyphenate('wrongpath.tst');
    }

    private function writeTempFile(): void
    {
        file_put_contents('tests/' . self::TEMP_FILE, self::TEST_CASE);
    }

    private function createTempFile(): void
    {
        $file = fopen('tests/' . self::TEMP_FILE, 'w');
        fclose($file);
    }

    private function deleteTempFile(): void
    {
        unlink('tests/' . self::TEMP_FILE);
    }

}