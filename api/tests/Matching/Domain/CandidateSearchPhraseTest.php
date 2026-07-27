<?php

declare(strict_types=1);

namespace Tests\Matching\Domain;

use Matching\Domain\CandidateSearchPhrase;
use PHPUnit\Framework\TestCase;

final class CandidateSearchPhraseTest extends TestCase
{
    public function testRolesAndSkillsAreCombined(): void
    {
        $phrase = new CandidateSearchPhrase(['Backend Developer'], ['PHP', 'Symfony']);

        self::assertSame('Backend Developer. Skills: PHP, Symfony', $phrase->value);
    }

    public function testMultipleRolesAreJoined(): void
    {
        $phrase = new CandidateSearchPhrase(['Backend Developer', 'Data Engineer'], []);

        self::assertSame('Backend Developer, Data Engineer', $phrase->value);
    }

    public function testEmptySkillsAreOmittedRatherThanLeavingATrailingLabel(): void
    {
        $phrase = new CandidateSearchPhrase(['Backend Developer'], []);

        self::assertSame('Backend Developer', $phrase->value);
    }

    public function testNoRolesAndNoSkillsProducesAnEmptyPhrase(): void
    {
        $phrase = new CandidateSearchPhrase([], []);

        self::assertSame('', $phrase->value);
    }
}
