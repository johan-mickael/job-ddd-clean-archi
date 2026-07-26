<?php

declare(strict_types=1);

namespace Tests\CandidateProfile\Domain;

use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\Exception\InvalidCvProfileData;
use CandidateProfile\Domain\SeniorityLevel;
use PHPUnit\Framework\TestCase;

final class CandidateProfileTest extends TestCase
{
    public function testEmptyDomainsAreAllowed(): void
    {
        $profile = $this->extractedFrom(domains: []);

        self::assertSame([], $profile->domains);
    }

    public function testAtLeastOneRoleIsRequired(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->extractedFrom(roles: []);
    }

    public function testBlankDomainAndRoleEntriesAreDropped(): void
    {
        $profile = $this->extractedFrom(domains: ['backend', '  '], roles: ['Backend Developer', '']);

        self::assertSame(['backend'], $profile->domains);
        self::assertSame(['Backend Developer'], $profile->roles);
    }

    public function testABlankSourceFileNameIsRejected(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        CandidateProfile::extractedFrom(
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            '  ',
            new \DateTimeImmutable('2026-07-26'),
            SeniorityLevel::Senior,
            ['backend'],
            ['Backend Developer'],
            [],
            [],
        );
    }

    /**
     * @param list<string> $domains
     * @param list<string> $roles
     */
    private function extractedFrom(array $domains = ['backend'], array $roles = ['Backend Developer']): CandidateProfile
    {
        return CandidateProfile::extractedFrom(
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
            SeniorityLevel::Senior,
            $domains,
            $roles,
            [],
            [],
        );
    }
}
