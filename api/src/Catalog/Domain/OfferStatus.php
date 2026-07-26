<?php

declare(strict_types=1);

namespace Catalog\Domain;

enum OfferStatus: string
{
    case Published = 'published';
    case Expired = 'expired';
}
