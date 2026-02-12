<?php

namespace App\Tests\Service;

use App\Service\PdfMergeService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfMergeServiceTest extends TestCase
{
    public function testMergeWithLessThanTwoFilesThrowsException(): void
    {
        $service = new PdfMergeService();

        $this->expectException(\InvalidArgumentException::class);

        $service->merge([]);
    }

    public function testMergeWithWrongFileType(): void
    {
        $service = new PdfMergeService();


        $file1 = new UploadedFile(
            __DIR__.'/../fixtures/test.txt',
            'test.txt',
            'text/plain',
            null,
            true
        );

        $file2 = new UploadedFile(
            __DIR__.'/../fixtures/test.txt',
            'test.txt',
            'text/plain',
            null,
            true
        );
        $this->expectException(\InvalidArgumentException::class);

        $service->merge([$file1, $file2]);
    }

    public function testMergeCreatesImgFile(): void
    {
        $service = new PdfMergeService();

        $file1 = new UploadedFile(
            __DIR__.'/../fixtures/sample1.png',
            'sample1.png',
            'image/png',
            null,
            true
        );

        $file2 = new UploadedFile(
            __DIR__.'/../fixtures/sample2.png',
            'sample2.png',
            'image/png',
            null,
            true
        );

        $result = $service->merge([$file1, $file2]);

        $this->assertFileExists($result);
        $this->assertGreaterThan(0, filesize($result));

        unlink($result);
    }
}
