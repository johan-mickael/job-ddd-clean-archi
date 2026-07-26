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

    public function testItTranslatesEveryEnrichmentField(): void
    {
        $offer = (new JobOfferPayloadTranslator())->translate($this->fullyPopulatedRawOffer());

        self::assertSame('https://cdn.francetravail.fr/logos/168NPMR.png', $offer->companyLogoUrl);
        self::assertSame('Éditeur de logiciels RH.', $offer->companyDescription);
        self::assertSame('Lyon - 69003', $offer->locationLabel);
        self::assertSame('69383', $offer->locationInseeCode);
        self::assertSame('69003', $offer->locationPostalCode);
        self::assertSame('M1805', $offer->romeCode);
        self::assertSame('Études et développement informatique', $offer->romeLabel);
        self::assertSame('required', $offer->experienceRequirement);
        self::assertTrue($offer->isWorkStudy);
        self::assertSame('40 - 50 k€ brut annuel', $offer->salaryDescription);
        self::assertSame(2, $offer->numberOfPositions);
        self::assertEquals(new \DateTimeImmutable('2026-07-10T09:00:00.000Z'), $offer->updatedAt);

        self::assertCount(2, $offer->skills);
        self::assertSame('PHP', $offer->skills[0]->label);
        self::assertSame('104232', $offer->skills[0]->romeCompetencyCode);
        self::assertTrue($offer->skills[0]->isRequired);
        self::assertSame('Symfony', $offer->skills[1]->label);
        self::assertFalse($offer->skills[1]->isRequired);
    }

    public function testItDropsSkillEntriesMissingALabel(): void
    {
        $raw = $this->rawOffer();
        $raw['competences'] = [['code' => '104232', 'exigence' => 'E']];

        $offer = (new JobOfferPayloadTranslator())->translate($raw);

        self::assertSame([], $offer->skills);
    }

    public function testEnrichmentFieldsAreNullWhenAbsentFromThePayload(): void
    {
        $offer = (new JobOfferPayloadTranslator())->translate($this->rawOffer());

        self::assertNull($offer->companyLogoUrl);
        self::assertNull($offer->locationLabel);
        self::assertNull($offer->romeCode);
        self::assertNull($offer->experienceRequirement);
        self::assertFalse($offer->isWorkStudy);
        self::assertNull($offer->numberOfPositions);
        self::assertSame([], $offer->skills);
        self::assertNull($offer->updatedAt);
    }

    public function testIncomingJobOfferHasNoFieldThatCouldCarryContactOrAgencyData(): void
    {
        self::assertFalse(\property_exists(IncomingJobOffer::class, 'contact'));
        self::assertFalse(\property_exists(IncomingJobOffer::class, 'agence'));
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

    /**
     * @return array<string, mixed>
     */
    private function fullyPopulatedRawOffer(): array
    {
        return [
            ...$this->rawOffer(),
            'dateActualisation' => '2026-07-10T09:00:00.000Z',
            'romeCode' => 'M1805',
            'romeLibelle' => 'Études et développement informatique',
            'experienceExige' => 'E',
            'alternance' => true,
            'nombrePostes' => 2,
            'entreprise' => [
                'nom' => 'Acme Corp',
                'description' => 'Éditeur de logiciels RH.',
                'logo' => 'https://cdn.francetravail.fr/logos/168NPMR.png',
            ],
            'lieuTravail' => [
                'libelle' => 'Lyon - 69003',
                'commune' => '69383',
                'codepostal' => '69003',
            ],
            'salaire' => [
                'libelle' => '40 - 50 k€ brut annuel',
            ],
            'competences' => [
                ['code' => '104232', 'libelle' => 'PHP', 'exigence' => 'E'],
                ['code' => null, 'libelle' => 'Symfony', 'exigence' => 'S'],
            ],
            'agence' => [
                'telephone' => '0400000000',
                'courriel' => 'agence.lyon@francetravail.fr',
            ],
        ];
    }
}
