<?php

declare(strict_types=1);

namespace Tests\CandidateProfile\Infrastructure\Persistence\Doctrine;

use CandidateProfile\Domain\CandidateLanguage;
use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateSkill;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;
use CandidateProfile\Domain\LanguageProficiency;
use CandidateProfile\Domain\SeniorityLevel;
use CandidateProfile\Infrastructure\Persistence\Doctrine\CandidateProfileEntity;
use CandidateProfile\Infrastructure\Persistence\Doctrine\CandidateProfileMapper;
use CandidateProfile\Infrastructure\Persistence\Doctrine\DoctrineCandidateProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineCandidateProfileRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrineCandidateProfileRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;
        $this->repository = new DoctrineCandidateProfileRepository($this->entityManager, new CandidateProfileMapper());
        $this->entityManager->createQuery('DELETE FROM ' . CandidateProfileEntity::class)->execute();
    }

    public function testANewlyAddedProfileCanBeReadBackByIdWithFullDetails(): void
    {
        $this->repository->add($this->aFullyDetailedProfile());
        $this->entityManager->clear();

        $profile = $this->repository->ofId(new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'));

        self::assertSame('cv.pdf', $profile->sourceFileName);
        self::assertSame(SeniorityLevel::Senior, $profile->seniority);
        self::assertSame(['backend'], $profile->domains);
        self::assertSame(['Backend Developer'], $profile->roles);
        self::assertCount(1, $profile->languages);
        self::assertSame('French', $profile->languages[0]->name);
        self::assertCount(1, $profile->skills);
        self::assertSame(5, $profile->skills[0]->yearsOfExperience);
    }

    public function testOfIdThrowsWhenTheProfileDoesNotExist(): void
    {
        $this->expectException(CandidateProfileNotFound::class);

        $this->repository->ofId(new CandidateProfileId('0198a1b2-0000-7000-8000-000000000099'));
    }

    private function aFullyDetailedProfile(): CandidateProfile
    {
        return CandidateProfile::extractedFrom(
            new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001'),
            'cv.pdf',
            new \DateTimeImmutable('2026-07-26'),
            SeniorityLevel::Senior,
            ['backend'],
            ['Backend Developer'],
            [new CandidateLanguage('French', LanguageProficiency::Native)],
            [new CandidateSkill('PHP', 5)],
        );
    }
}
