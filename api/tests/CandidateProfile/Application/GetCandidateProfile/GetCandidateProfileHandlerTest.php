<?php

declare(strict_types=1);

namespace Tests\CandidateProfile\Application\GetCandidateProfile;

use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfile;
use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfileHandler;
use CandidateProfile\Domain\CandidateLanguage;
use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateSkill;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;
use CandidateProfile\Domain\LanguageProficiency;
use CandidateProfile\Domain\SeniorityLevel;
use PHPUnit\Framework\TestCase;
use Tests\CandidateProfile\InMemoryCandidateProfileRepository;

final class GetCandidateProfileHandlerTest extends TestCase
{
    public function testItReturnsAViewOfTheStoredProfile(): void
    {
        $profiles = new InMemoryCandidateProfileRepository();
        $profiles->add(CandidateProfile::extractedFrom(
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
            SeniorityLevel::Senior,
            ['backend'],
            ['Backend Developer'],
            [new CandidateLanguage('French', LanguageProficiency::Native)],
            [new CandidateSkill('PHP', 5)],
        ));
        $handler = new GetCandidateProfileHandler($profiles);

        $view = $handler(new GetCandidateProfile('0198a1b2-0000-7000-8000-000000000001'));

        self::assertSame('senior', $view->seniority);
        self::assertSame(['backend'], $view->domains);
        self::assertSame('PHP', $view->skills[0]->label);
        self::assertSame('French', $view->languages[0]->name);
    }

    public function testItThrowsWhenTheProfileDoesNotExist(): void
    {
        $handler = new GetCandidateProfileHandler(new InMemoryCandidateProfileRepository());

        $this->expectException(CandidateProfileNotFound::class);

        $handler(new GetCandidateProfile('0198a1b2-0000-7000-8000-000000000099'));
    }
}
