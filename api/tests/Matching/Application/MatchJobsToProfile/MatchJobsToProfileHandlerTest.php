<?php

declare(strict_types=1);

namespace Tests\Matching\Application\MatchJobsToProfile;

use CandidateProfile\Application\GetCandidateProfile\CandidateProfileSkillView;
use CandidateProfile\Application\GetCandidateProfile\CandidateProfileView;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;
use Matching\Application\MatchJobsToProfile\MatchJobsToProfile;
use Matching\Application\MatchJobsToProfile\MatchJobsToProfileHandler;
use PHPUnit\Framework\TestCase;
use Search\Domain\SearchResultItem;
use Search\Domain\SearchResults;
use Tests\Matching\FakeGetCandidateProfileHandler;
use Tests\Matching\FakeSearchJobsHandler;

final class MatchJobsToProfileHandlerTest extends TestCase
{
    public function testItSearchesUsingTheProfilesRolesAndSkills(): void
    {
        $profile = $this->aProfileView();
        $searchJobs = new FakeSearchJobsHandler(new SearchResults([], 1, 20, 0));
        $handler = new MatchJobsToProfileHandler(new FakeGetCandidateProfileHandler($profile), $searchJobs);

        $handler(new MatchJobsToProfile('0198a1b2-0000-7000-8000-000000000001', 'owner-1'));

        self::assertSame('Backend Developer. Skills: PHP, Symfony', $searchJobs->lastQuery?->freeText);
    }

    public function testMatchedJobsMirrorTheSearchResults(): void
    {
        $item = new SearchResultItem(
            '168NPMR',
            'Développeur PHP',
            'Acme Corp',
            'Lyon',
            'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            new \DateTimeImmutable('2026-07-01'),
            0.9,
        );
        $searchJobs = new FakeSearchJobsHandler(new SearchResults([$item], 1, 20, 1));
        $handler = new MatchJobsToProfileHandler(new FakeGetCandidateProfileHandler($this->aProfileView()), $searchJobs);

        $matches = $handler(new MatchJobsToProfile('0198a1b2-0000-7000-8000-000000000001', 'owner-1'));

        self::assertCount(1, $matches->items);
        self::assertSame('168NPMR', $matches->items[0]->jobId);
        self::assertSame(1, $matches->total);
    }

    public function testItPropagatesCandidateProfileNotFound(): void
    {
        $handler = new MatchJobsToProfileHandler(
            new FakeGetCandidateProfileHandler(null),
            new FakeSearchJobsHandler(new SearchResults([], 1, 20, 0)),
        );

        $this->expectException(CandidateProfileNotFound::class);

        $handler(new MatchJobsToProfile('0198a1b2-0000-7000-8000-000000000099', 'owner-1'));
    }

    private function aProfileView(): CandidateProfileView
    {
        return new CandidateProfileView(
            candidateProfileId: '0198a1b2-0000-7000-8000-000000000001',
            sourceFileName: 'cv.pdf',
            extractedAt: new \DateTimeImmutable('2026-07-26'),
            seniority: 'senior',
            domains: ['backend'],
            roles: ['Backend Developer'],
            languages: [],
            skills: [
                new CandidateProfileSkillView('PHP', 5),
                new CandidateProfileSkillView('Symfony', 5),
            ],
        );
    }
}
