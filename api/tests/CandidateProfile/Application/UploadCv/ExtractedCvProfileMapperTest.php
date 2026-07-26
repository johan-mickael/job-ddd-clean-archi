<?php

declare(strict_types=1);

namespace Tests\CandidateProfile\Application\UploadCv;

use CandidateProfile\Application\UploadCv\ExtractedCvProfileMapper;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\Exception\InvalidCvProfileData;
use CandidateProfile\Domain\SeniorityLevel;
use PHPUnit\Framework\TestCase;

final class ExtractedCvProfileMapperTest extends TestCase
{
    private ExtractedCvProfileMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ExtractedCvProfileMapper();
    }

    public function testAWellFormedResponseMapsToACandidateProfile(): void
    {
        $profile = $this->mapper->toCandidateProfile(
            [
                'seniority' => 'senior',
                'domains' => ['backend', 'e-commerce'],
                'roles' => ['Backend Developer'],
                'languages' => [['name' => 'French', 'proficiency' => 'native']],
                'skills' => [['label' => 'PHP', 'yearsOfExperience' => 5]],
            ],
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );

        self::assertSame(SeniorityLevel::Senior, $profile->seniority);
        self::assertSame(['backend', 'e-commerce'], $profile->domains);
        self::assertSame('PHP', $profile->skills[0]->label);
        self::assertSame(5, $profile->skills[0]->yearsOfExperience);
    }

    public function testASkillWithoutYearsOfExperienceIsAccepted(): void
    {
        $profile = $this->mapper->toCandidateProfile(
            $this->validPayload(['skills' => [['label' => 'PHP']]]),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );

        self::assertNull($profile->skills[0]->yearsOfExperience);
    }

    public function testAnUnrecognizedSeniorityValueIsRejected(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            $this->validPayload(['seniority' => 'godlike']),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    public function testANonStringSeniorityIsRejected(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            $this->validPayload(['seniority' => 7]),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    public function testDomainsMustBeAnArray(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            $this->validPayload(['domains' => 'backend']),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    public function testEveryDomainEntryMustBeAString(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            $this->validPayload(['domains' => [42]]),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    public function testAnUnrecognizedLanguageProficiencyIsRejected(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            $this->validPayload(['languages' => [['name' => 'French', 'proficiency' => 'perfect']]]),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    public function testASkillWithoutALabelIsRejected(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            $this->validPayload(['skills' => [['yearsOfExperience' => 3]]]),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    public function testANonIntegerYearsOfExperienceIsRejected(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            $this->validPayload(['skills' => [['label' => 'PHP', 'yearsOfExperience' => 'five']]]),
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    public function testMissingRequiredFieldsAreRejected(): void
    {
        $this->expectException(InvalidCvProfileData::class);

        $this->mapper->toCandidateProfile(
            ['seniority' => 'senior'],
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
        );
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [...[
            'seniority' => 'senior',
            'domains' => ['backend'],
            'roles' => ['Backend Developer'],
            'languages' => [],
            'skills' => [],
        ], ...$overrides];
    }
}
