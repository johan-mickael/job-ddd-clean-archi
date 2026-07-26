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
    ) {
        $this->status = OfferStatus::Published;
    }

    public static function publish(
        JobId $id,
        string $title,
        string $description,
        string $companyName,
        ContractType $contractType,
        ApplyUrl $applyUrl,
        \DateTimeImmutable $publishedAt,
    ): self {
        return new self($id, $title, $description, $companyName, $contractType, $applyUrl, $publishedAt);
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
