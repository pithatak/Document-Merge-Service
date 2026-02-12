<?php

namespace App\Service;

use setasign\Fpdi\Tcpdf\Fpdi;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfMergeService
{
    public function merge(array $files): string
    {
        if (count($files) < 2) {
            throw new \InvalidArgumentException('Minimum 2 files required.');
        }

        $pdf = new Fpdi();

        foreach ($files as $file) {
            /** @var UploadedFile $file */

            $mimeType = $file->getMimeType();

            if ($mimeType === 'application/pdf') {
                $pageCount = $pdf->setSourceFile($file->getPathname());

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            } elseif (str_starts_with($mimeType, 'image/')) {
                $pdf->AddPage();
                $pdf->Image($file->getPathname(), 10, 10, 190);
            } else {
                throw new \InvalidArgumentException('Unsupported file type.');
            }
        }

        $outputPath = sys_get_temp_dir() . '/merged_' . uniqid() . '.pdf';
        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }
}
