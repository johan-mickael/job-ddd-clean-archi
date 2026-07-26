<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

use Ingestion\Domain\Exception\JobOfferPayloadInvalid;
use Ingestion\Domain\IncomingJobOffer;

// Anti-corruption layer: FT's raw offer JSON never reaches past this point.
// contact.nom/telephone/courriel are deliberately never read — IncomingJobOffer
// has no field that could carry them onward (license §7.3).
final class JobOfferPayloadTranslator
{
    private const array CONTRACT_TYPE_CODES = [
        'CDI' => 'cdi',
        'CDD' => 'cdd',
        'MIS' => 'interim',
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
        );
    }

    /**
     * @param array<string, mixed> $rawOffer
     */
    private function companyName(array $rawOffer): string
    {
        $entreprise = $rawOffer['entreprise'] ?? [];
        $name = \is_array($entreprise) ? ($entreprise['nom'] ?? null) : null;

        return \is_string($name) && $name !== '' ? $name : 'Entreprise non communiquée';
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
        $origin = $rawOffer['origineOffre'] ?? [];
        $url = \is_array($origin) ? ($origin['urlOrigine'] ?? null) : null;

        if (!\is_string($url) || $url === '') {
            throw JobOfferPayloadInvalid::missingField('origineOffre.urlOrigine');
        }

        return $url;
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
