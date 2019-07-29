<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FileHyphenationAlgorithmTest extends TestCase
{
    private $hyphenation;
    private const RESULT_FOR_TEST_CASE = 'Mis-trans-late is a fea-ture.';
    private const TEST_CASE = 'Mistranslate is a feature.';
    private const TEMP_FILE = 'test.txt';

    protected function setUp(): void
    {
        $string = $this->createMock(\NXT\Algorithms\StringHyphenation::class);
        $string->expects($this->once())
            ->method('hyphenate')
            ->with(self::TEST_CASE)
            ->willReturn(self::RESULT_FOR_TEST_CASE);

        $this->hyphenation = new \NXT\Algorithms\FileHyphenation($string);
        $this->createTempFile();
        $this->writeTempFile();
    }

    protected function tearDown(): void
    {
        $this->hyphenation = null;
        $this->deleteTempFile();
    }

    public function testFileHyphenation()
    {
        $result = $this->hyphenation->hyphenate('../tests/' . self::TEMP_FILE);
        $this->assertEquals(self::RESULT_FOR_TEST_CASE, $result);
    }

    private function writeTempFile(): void
    {
        file_put_contents('tests/' . self::TEMP_FILE, self::TEST_CASE);
    }

    private function createTempFile(): void
    {
        $file = fopen('tests/' . self::TEMP_FILE, 'w') or die('Cannot open temp. file.');
        fclose($file);
    }

    private function deleteTempFile(): void
    {
        unlink('tests/' . self::TEMP_FILE) or die('Cannot delete temp. file');
    }

}