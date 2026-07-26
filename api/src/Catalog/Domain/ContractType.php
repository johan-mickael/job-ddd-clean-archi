<?php

declare(strict_types=1);

namespace Catalog\Domain;

enum ContractType: string
{
    case PermanentContract = 'cdi';
    case FixedTermContract = 'cdd';
    case TemporaryWork = 'interim';
    case Other = 'other';
}
