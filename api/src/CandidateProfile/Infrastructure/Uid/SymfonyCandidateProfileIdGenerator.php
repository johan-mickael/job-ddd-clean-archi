<?php

declare(strict_types=1);

namespace CandidateProfile\Infrastructure\Uid;

use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateProfileIdGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class SymfonyCandidateProfileIdGenerator implements CandidateProfileIdGeneratorInterface
{
    public function next(): CandidateProfileId
    {
        return new CandidateProfileId(Uuid::v7()->toRfc4122());
    }
}
