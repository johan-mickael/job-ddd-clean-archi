<?php

declare(strict_types=1);

namespace Catalog\Domain;

enum ExperienceRequirement: string
{
    case BeginnerAccepted = 'beginner_accepted';
    case Desired = 'desired';
    case Required = 'required';
}
