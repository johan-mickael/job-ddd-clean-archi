<?php

declare(strict_types=1);

namespace Catalog\Domain;

use Catalog\Domain\Exception\OfferAlreadyExpired;

final class Job
{
    private OfferStatus $status;

    private function __construct(
        public readonly JobId $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $companyName,
        public readonly ContractType $contractType,
        public readonly ApplyUrl $applyUrl,
        public readonly \DateTimeImmutable $publishedAt,
        public readonly JobDetails $details,
        OfferStatus $status,
    ) {
        $this->status = $status;
    }

    public static function publish(
        JobId $id,
        string $title,
        string $description,
        string $companyName,
        ContractType $contractType,
        ApplyUrl $applyUrl,
        \DateTimeImmutable $publishedAt,
        JobDetails $details = new JobDetails(),
    ): self {
        return new self($id, $title, $description, $companyName, $contractType, $applyUrl, $publishedAt, $details, OfferStatus::Published);
    }

    /**
     * Rehydrates a Job from storage. Not a business event - use publish() for that.
     */
    public static function reconstitute(
        JobId $id,
        string $title,
        string $description,
        string $companyName,
        ContractType $contractType,
        ApplyUrl $applyUrl,
        \DateTimeImmutable $publishedAt,
        JobDetails $details,
        OfferStatus $status,
    ): self {
        return new self($id, $title, $description, $companyName, $contractType, $applyUrl, $publishedAt, $details, $status);
    }

    public function expire(): void
    {
        if ($this->status === OfferStatus::Expired) {
            throw OfferAlreadyExpired::forJob($this->id);
        }

        $this->status = OfferStatus::Expired;
    }

    public function status(): OfferStatus
    {
        return $this->status;
    }

    public function isExpired(): bool
    {
        return $this->status === OfferStatus::Expired;
    }
}
