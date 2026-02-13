<?php

namespace App\Controller\Api;

use App\DTO\MergeRequest;
use App\Service\PdfMergeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class PdfMergeController extends AbstractController
{
    #[Route('/api/merge', name: 'api_merge', methods: ['POST'])]
    public function merge(
        Request            $request,
        PdfMergeService    $mergeService,
        ValidatorInterface $validator
    ): Response
    {

        $files = $request->files->get('files', []);
        $dto = new MergeRequest($files);

        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $formattedErrors = [];

            foreach ($errors as $error) {
                $formattedErrors[] = $error->getMessage();
            }

            return $this->json([
                'errors' => $formattedErrors
            ], 400);
        }


        $mergedPath = $mergeService->merge($dto->files);
        $response = new BinaryFileResponse($mergedPath);
        $response->deleteFileAfterSend();

        return $response;

    }
}
