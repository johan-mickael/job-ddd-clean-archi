<?php

declare(strict_types=1);

namespace CandidateProfile\Presentation\Http;

use CandidateProfile\Application\UploadCv\UploadCv;
use CandidateProfile\Application\UploadCv\UploadCvHandlerInterface;
use CandidateProfile\Domain\Exception\CvProfileExtractionFailed;
use CandidateProfile\Domain\Exception\CvTextNotExtractable;
use CandidateProfile\Domain\Exception\InvalidCvProfileData;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UploadCvController
{
    public function __construct(private readonly UploadCvHandlerInterface $uploadCv) {}

    #[Route('/api/cv', name: 'upload_cv', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $file = $request->files->get('cv');

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return new JsonResponse(['error' => 'A valid "cv" file upload is required.'], 400);
        }

        $content = \file_get_contents($file->getPathname());
        \assert(\is_string($content));

        try {
            $id = ($this->uploadCv)(new UploadCv($content, $file->getClientOriginalName()));
        } catch (CvTextNotExtractable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (InvalidCvProfileData $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (CvProfileExtractionFailed) {
            return new JsonResponse(['error' => 'The CV could not be processed right now. Try again in a moment.'], 502);
        }

        return new JsonResponse(['candidateProfileId' => $id->value], 201);
    }
}
