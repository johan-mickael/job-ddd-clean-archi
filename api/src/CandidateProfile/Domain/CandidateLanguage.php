<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

use CandidateProfile\Domain\Exception\InvalidCvProfileData;

final readonly class CandidateLanguage
{
    public function __construct(
        public string $name,
        public LanguageProficiency $proficiency,
    ) {
        if (\trim($this->name) === '') {
            throw InvalidCvProfileData::becauseOfField('languages[].name', 'must not be blank.');
        }
    }
}
