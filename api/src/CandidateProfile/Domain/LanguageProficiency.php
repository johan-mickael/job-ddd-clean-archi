<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

enum LanguageProficiency: string
{
    case Basic = 'basic';
    case Conversational = 'conversational';
    case Fluent = 'fluent';
    case Native = 'native';
}
