<?php

declare(strict_types=1);

namespace Tests\Catalog\Domain;

use Catalog\Domain\ApplyUrl;
use Catalog\Domain\Exception\InvalidApplyUrl;
use PHPUnit\Framework\TestCase;

final class ApplyUrlTest extends TestCase
{
    public function testAnHttpsUrlIsAccepted(): void
    {
        $url = new ApplyUrl('https://candidat.francetravail.fr/offres/recherche/detail/168NPMR');

        self::assertSame('https://candidat.francetravail.fr/offres/recherche/detail/168NPMR', $url->value);
    }

    public function testABlankValueIsRejected(): void
    {
        $this->expectException(InvalidApplyUrl::class);

        new ApplyUrl('');
    }

    public function testANonHttpSchemeIsRejected(): void
    {
        $this->expectException(InvalidApplyUrl::class);

        new ApplyUrl('ftp://example.com/offer');
    }
}
