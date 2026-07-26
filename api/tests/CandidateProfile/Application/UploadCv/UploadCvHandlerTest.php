<?php

declare(strict_types=1);

namespace Tests\CandidateProfile\Application\UploadCv;

use CandidateProfile\Application\UploadCv\ExtractedCvProfileMapper;
use CandidateProfile\Application\UploadCv\UploadCv;
use CandidateProfile\Application\UploadCv\UploadCvHandler;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\Exception\InvalidCvProfileData;
use PHPUnit\Framework\TestCase;
use Tests\CandidateProfile\FakeCvProfileExtractor;
use Tests\CandidateProfile\FakeTextExtractor;
use Tests\CandidateProfile\FixedCandidateProfileIdGenerator;
use Tests\CandidateProfile\InMemoryCandidateProfileRepository;

final class UploadCvHandlerTest extends TestCase
{
    public function testAWellFormedCvIsExtractedAndPersisted(): void
    {
        $id = new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001');
        $profiles = new InMemoryCandidateProfileRepository();
        $handler = new UploadCvHandler(
            new FakeTextExtractor(),
            new FakeCvProfileExtractor(),
            new ExtractedCvProfileMapper(),
            new FixedCandidateProfileIdGenerator($id),
            $profiles,
        );

        $returnedId = $handler(new UploadCv('%PDF-1.4 raw bytes', 'cv.pdf'));

        self::assertTrue($returnedId->equals($id));
        self::assertSame('cv.pdf', $profiles->ofId($id)->sourceFileName);
    }

    public function testMalformedLlmOutputIsRejectedRatherThanPersisted(): void
    {
        $profiles = new InMemoryCandidateProfileRepository();
        $handler = new UploadCvHandler(
            new FakeTextExtractor(),
            new FakeCvProfileExtractor(['seniority' => 'not-a-real-level', 'domains' => [], 'roles' => [], 'languages' => [], 'skills' => []]),
            new ExtractedCvProfileMapper(),
            new FixedCandidateProfileIdGenerator(new CandidateProfileId('0198a1b2-0000-7000-8000-000000000001')),
            $profiles,
        );

        $this->expectException(InvalidCvProfileData::class);

        $handler(new UploadCv('%PDF-1.4 raw bytes', 'cv.pdf'));
    }
}
