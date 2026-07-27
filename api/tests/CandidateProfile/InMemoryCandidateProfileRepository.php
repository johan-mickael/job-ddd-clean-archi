<?php

declare(strict_types=1);

namespace Tests\CandidateProfile;

use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateProfileRepositoryInterface;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;

final class InMemoryCandidateProfileRepository implements CandidateProfileRepositoryInterface
{
    /** @var array<string, CandidateProfile> */
    private array $profiles = [];

    public function ofId(CandidateProfileId $id): CandidateProfile
    {
        return $this->profiles[$id->value] ?? throw CandidateProfileNotFound::withId($id);
    }

    public function add(CandidateProfile $profile): void
    {
        $this->profiles[$profile->id->value] = $profile;
    }

    public function ofOwnerId(string $ownerId): array
    {
        return \array_values(\array_filter(
            $this->profiles,
            static fn(CandidateProfile $profile): bool => $profile->ownerId === $ownerId,
        ));
    }
}
