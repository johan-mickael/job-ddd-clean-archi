<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

enum SeniorityLevel: string
{
    case Junior = 'junior';
    case Mid = 'mid';
    case Senior = 'senior';
    case Lead = 'lead';
    case Principal = 'principal';
}
