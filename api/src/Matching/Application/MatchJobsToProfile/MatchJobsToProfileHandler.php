<?php

declare(strict_types=1);

namespace Matching\Application\MatchJobsToProfile;

use CandidateProfile\Application\GetCandidateProfile\CandidateProfileSkillView;
use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfile;
use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfileHandlerInterface;
use Matching\Domain\CandidateSearchPhrase;
use Search\Application\SearchJobs\SearchJobs;
use Search\Application\SearchJobs\SearchJobsHandlerInterface;

final class MatchJobsToProfileHandler implements MatchJobsToProfileHandlerInterface
{
    public function __construct(
        private readonly GetCandidateProfileHandlerInterface $getCandidateProfile,
        private readonly SearchJobsHandlerInterface $searchJobs,
    ) {}

    public function __invoke(MatchJobsToProfile $query): MatchedJobs
    {
        $profile = ($this->getCandidateProfile)(new GetCandidateProfile($query->candidateProfileId));

        $phrase = new CandidateSearchPhrase(
            $profile->roles,
            \array_map(static fn(CandidateProfileSkillView $skill): string => $skill->label, $profile->skills),
        );

        $results = ($this->searchJobs)(new SearchJobs(
            freeText: $phrase->value,
            page: $query->page,
            pageSize: $query->pageSize,
        ));

        return new MatchedJobs(
            items: \array_map($this->toMatchedJobView(...), $results->items),
            page: $results->page,
            pageSize: $results->pageSize,
            total: $results->total,
            totalPages: $results->totalPages(),
        );
    }

    /**
     * $item is really a Search\Domain\SearchResultItem, but Application here
     * may only depend on Search's Application layer, not its Domain - typed
     * structurally instead of naming that class, so the boundary holds
     * while PHPStan still checks every property access.
     *
     * @param object{jobId: string, title: string, companyName: string, locationLabel: ?string, applyUrl: string, publishedAt: \DateTimeImmutable, score: float} $item
     */
    private function toMatchedJobView(object $item): MatchedJobView
    {
        return new MatchedJobView(
            jobId: $item->jobId,
            title: $item->title,
            companyName: $item->companyName,
            locationLabel: $item->locationLabel,
            applyUrl: $item->applyUrl,
            publishedAt: $item->publishedAt,
            score: $item->score,
        );
    }
}
