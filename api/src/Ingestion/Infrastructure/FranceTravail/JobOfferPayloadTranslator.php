<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

use Ingestion\Domain\Exception\JobOfferPayloadInvalid;
use Ingestion\Domain\IncomingJobOffer;
use Ingestion\Domain\IncomingJobSkill;

// Anti-corruption layer: FT's raw offer JSON never reaches past this point.
// contact.* and agence.* are deliberately never read — IncomingJobOffer has
// no field that could carry them onward (license §7.3).
final class JobOfferPayloadTranslator
{
    private const array CONTRACT_TYPE_CODES = [
        'CDI' => 'cdi',
        'CDD' => 'cdd',
        'MIS' => 'interim',
    ];

    private const array EXPERIENCE_REQUIREMENT_CODES = [
        'D' => 'beginner_accepted',
        'S' => 'desired',
        'E' => 'required',
    ];

    /**
     * @param array<string, mixed> $rawOffer
     */
    public function translate(array $rawOffer): IncomingJobOffer
    {
        return new IncomingJobOffer(
            offerId: $this->requireString($rawOffer, 'id'),
            title: $this->requireString($rawOffer, 'intitule'),
            description: $this->requireString($rawOffer, 'description'),
            companyName: $this->companyName($rawOffer),
            contractType: $this->contractType($rawOffer),
            applyUrl: $this->applyUrl($rawOffer),
            publishedAt: new \DateTimeImmutable($this->requireString($rawOffer, 'dateCreation')),
            companyLogoUrl: $this->optionalString($rawOffer, 'entreprise', 'logo'),
            companyDescription: $this->optionalString($rawOffer, 'entreprise', 'description'),
            locationLabel: $this->optionalString($rawOffer, 'lieuTravail', 'libelle'),
            locationInseeCode: $this->optionalString($rawOffer, 'lieuTravail', 'commune'),
            locationPostalCode: $this->optionalString($rawOffer, 'lieuTravail', 'codepostal'),
            romeCode: $this->nullableString($rawOffer['romeCode'] ?? null),
            romeLabel: $this->nullableString($rawOffer['romeLibelle'] ?? null),
            experienceRequirement: $this->experienceRequirement($rawOffer),
            isWorkStudy: ($rawOffer['alternance'] ?? false) === true,
            salaryDescription: $this->optionalString($rawOffer, 'salaire', 'libelle'),
            numberOfPositions: \is_int($rawOffer['nombrePostes'] ?? null) ? $rawOffer['nombrePostes'] : null,
            skills: $this->skills($rawOffer),
            updatedAt: $this->nullableDate($rawOffer['dateActualisation'] ?? null),
        );
    }

    /**
     * @param array<string, mixed> $rawOffer
     */
    private function companyName(array $rawOffer): string
    {
        $name = $this->optionalString($rawOffer, 'entreprise', 'nom');

        return $name ?? 'Entreprise non communiquée';
    }

    /**
     * @param array<string, mixed> $rawOffer
     */
    private function contractType(array $rawOffer): string
    {
        $code = $this->requireString($rawOffer, 'typeContrat');

        return self::CONTRACT_TYPE_CODES[$code] ?? 'other';
    }

    /**
     * @param array<string, mixed> $rawOffer
     */
    private function applyUrl(array $rawOffer): string
    {
        $url = $this->optionalString($rawOffer, 'origineOffre', 'urlOrigine');

        return $url ?? throw JobOfferPayloadInvalid::missingField('origineOffre.urlOrigine');
    }

    /**
     * @param array<string, mixed> $rawOffer
     */
    private function experienceRequirement(array $rawOffer): ?string
    {
        $code = $rawOffer['experienceExige'] ?? null;

        return \is_string($code) ? self::EXPERIENCE_REQUIREMENT_CODES[$code] ?? null : null;
    }

    /**
     * @param array<string, mixed> $rawOffer
     * @return list<IncomingJobSkill>
     */
    private function skills(array $rawOffer): array
    {
        $rawSkills = $rawOffer['competences'] ?? [];
        if (!\is_array($rawSkills)) {
            return [];
        }

        $skills = [];
        foreach ($rawSkills as $rawSkill) {
            $label = \is_array($rawSkill) ? ($rawSkill['libelle'] ?? null) : null;
            if (!\is_string($label) || $label === '') {
                continue;
            }

            $skills[] = new IncomingJobSkill(
                label: $label,
                romeCompetencyCode: \is_string($rawSkill['code'] ?? null) ? $rawSkill['code'] : null,
                isRequired: ($rawSkill['exigence'] ?? null) === 'E',
            );
        }

        return $skills;
    }

    /**
     * @param array<string, mixed> $rawOffer
     */
    private function optionalString(array $rawOffer, string $parentField, string $field): ?string
    {
        $parent = $rawOffer[$parentField] ?? [];

        return $this->nullableString(\is_array($parent) ? ($parent[$field] ?? null) : null);
    }

    private function nullableString(mixed $value): ?string
    {
        return \is_string($value) && $value !== '' ? $value : null;
    }

    private function nullableDate(mixed $value): ?\DateTimeImmutable
    {
        return \is_string($value) && $value !== '' ? new \DateTimeImmutable($value) : null;
    }

    /**
     * @param array<string, mixed> $rawOffer
     */
    private function requireString(array $rawOffer, string $field): string
    {
        $value = $rawOffer[$field] ?? null;

        if (!\is_string($value) || $value === '') {
            throw JobOfferPayloadInvalid::missingField($field);
        }

        return $value;
    }
}
