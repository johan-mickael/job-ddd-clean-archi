<?php

declare(strict_types=1);

namespace Tests\Ingestion\Infrastructure\FranceTravail;

use Ingestion\Domain\Exception\JobOfferPayloadInvalid;
use Ingestion\Domain\IncomingJobOffer;
use Ingestion\Infrastructure\FranceTravail\JobOfferPayloadTranslator;
use PHPUnit\Framework\TestCase;

final class JobOfferPayloadTranslatorTest extends TestCase
{
    public function testItTranslatesAWellFormedOffer(): void
    {
        $offer = (new JobOfferPayloadTranslator())->translate($this->rawOffer());

        self::assertSame('168NPMR', $offer->offerId);
        self::assertSame('Développeur PHP Senior', $offer->title);
        self::assertSame('Acme Corp', $offer->companyName);
        self::assertSame('cdi', $offer->contractType);
        self::assertSame('https://candidat.francetravail.fr/offres/recherche/detail/168NPMR', $offer->applyUrl);
    }

    public function testIncomingJobOfferHasNoFieldThatCouldCarryContactData(): void
    {
        self::assertFalse(\property_exists(IncomingJobOffer::class, 'contact'));
    }

    public function testItFallsBackToOtherForAnUnmappedContractTypeCode(): void
    {
        $raw = $this->rawOffer();
        $raw['typeContrat'] = 'SAI';

        $offer = (new JobOfferPayloadTranslator())->translate($raw);

        self::assertSame('other', $offer->contractType);
    }

    public function testItUsesAFallbackNameWhenTheCompanyIsNotDisclosed(): void
    {
        $raw = $this->rawOffer();
        unset($raw['entreprise']);

        $offer = (new JobOfferPayloadTranslator())->translate($raw);

        self::assertSame('Entreprise non communiquée', $offer->companyName);
    }

    public function testItRejectsAPayloadMissingARequiredField(): void
    {
        $raw = $this->rawOffer();
        unset($raw['intitule']);

        $this->expectException(JobOfferPayloadInvalid::class);

        (new JobOfferPayloadTranslator())->translate($raw);
    }

    /**
     * @return array<string, mixed>
     */
    private function rawOffer(): array
    {
        return [
            'id' => '168NPMR',
            'intitule' => 'Développeur PHP Senior',
            'description' => "Vous rejoignez une équipe backend...\n",
            'typeContrat' => 'CDI',
            'dateCreation' => '2026-07-01T08:00:00.000Z',
            'entreprise' => [
                'nom' => 'Acme Corp',
            ],
            'origineOffre' => [
                'urlOrigine' => 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            ],
            'contact' => [
                'nom' => 'Jeanne Dupont',
                'telephone' => '0102030405',
                'courriel' => 'jeanne.dupont@acme.example',
            ],
        ];
    }
}
