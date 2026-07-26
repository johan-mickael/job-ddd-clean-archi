<?php

declare(strict_types=1);

namespace Catalog\Presentation\Http;

use Catalog\Application\GetJob\GetJob;
use Catalog\Application\GetJob\GetJobHandlerInterface;
use Catalog\Application\GetJob\JobView;
use Catalog\Application\GetJob\JobViewSkill;
use Catalog\Domain\Exception\JobNotFound;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class JobController
{
    public function __construct(private readonly GetJobHandlerInterface $getJob) {}

    // Full detail, every field the API gave us plus the company logo -
    // license §7.4 requires the complete content on the detail view, even
    // though search-result summaries may stay partial.
    #[Route('/api/jobs/{jobId}', name: 'job_detail', methods: ['GET'])]
    public function __invoke(string $jobId): JsonResponse
    {
        try {
            $view = ($this->getJob)(new GetJob($jobId));
        } catch (JobNotFound) {
            return new JsonResponse(['error' => 'Job not found.'], 404);
        }

        return new JsonResponse($this->toArray($view));
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(JobView $view): array
    {
        return [
            'jobId' => $view->jobId,
            'title' => $view->title,
            'description' => $view->description,
            'companyName' => $view->companyName,
            'companyLogoUrl' => $view->companyLogoUrl,
            'companyDescription' => $view->companyDescription,
            'contractType' => $view->contractType,
            'applyUrl' => $view->applyUrl,
            'publishedAt' => $view->publishedAt->format(\DATE_ATOM),
            'updatedAt' => $view->updatedAt?->format(\DATE_ATOM),
            'status' => $view->status,
            'locationLabel' => $view->locationLabel,
            'locationInseeCode' => $view->locationInseeCode,
            'locationPostalCode' => $view->locationPostalCode,
            'romeCode' => $view->romeCode,
            'romeLabel' => $view->romeLabel,
            'experienceRequirement' => $view->experienceRequirement,
            'isWorkStudy' => $view->isWorkStudy,
            'salaryDescription' => $view->salaryDescription,
            'numberOfPositions' => $view->numberOfPositions,
            'skills' => \array_map($this->skillToArray(...), $view->skills),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function skillToArray(JobViewSkill $skill): array
    {
        return [
            'label' => $skill->label,
            'romeCompetencyCode' => $skill->romeCompetencyCode,
            'isRequired' => $skill->isRequired,
        ];
    }
}
