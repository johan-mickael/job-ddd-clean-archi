<?php

declare(strict_types=1);

namespace CandidateProfile\Application\UploadCv;

use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateProfileIdGeneratorInterface;
use CandidateProfile\Domain\CandidateProfileRepositoryInterface;
use CandidateProfile\Domain\CvProfileExtractorInterface;
use CandidateProfile\Domain\TextExtractorInterface;

final class UploadCvHandler implements UploadCvHandlerInterface
{
    public function __construct(
        private readonly TextExtractorInterface $textExtractor,
        private readonly CvProfileExtractorInterface $profileExtractor,
        private readonly ExtractedCvProfileMapper $mapper,
        private readonly CandidateProfileIdGeneratorInterface $idGenerator,
        private readonly CandidateProfileRepositoryInterface $profiles,
    ) {}

    public function __invoke(UploadCv $command): CandidateProfileId
    {
        $text = $this->textExtractor->extract($command->fileContent, $command->fileName);
        $raw = $this->profileExtractor->extract($text);

        $id = $this->idGenerator->next();
        $profile = $this->mapper->toCandidateProfile($raw, $id, $command->fileName, new \DateTimeImmutable());

        $this->profiles->add($profile);

        return $id;
    }
}
