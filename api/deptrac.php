<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\ClassNameRegexConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

return static function (DeptracConfig $config): void {
    $config
        ->paths('./src')
        ->excludeFiles('#Kernel\.php$#')
    ;

    // Every bounded context follows the same Domain <- Application <-
    // Infrastructure dependency rule (see the php-ddd-clean-architecture
    // skill). Cross-context calls may only reach another context's
    // Application layer (the published entry points/ports), never its
    // Domain or Infrastructure - that's the rule that keeps this a real
    // bounded-context architecture instead of a monolith with subfolders.
    $contexts = ['Catalog', 'Ingestion', 'Search', 'CandidateProfile', 'Matching', 'Identity'];

    $domains = [];
    $applications = [];
    $infrastructures = [];
    $presentations = [];

    foreach ($contexts as $context) {
        $domains[$context] = Layer::withName($context . 'Domain')->collectors(
            ClassNameRegexConfig::create('#^' . $context . '\\Domain\\#'),
        );
        $applications[$context] = Layer::withName($context . 'Application')->collectors(
            ClassNameRegexConfig::create('#^' . $context . '\\Application\\#'),
        );
        $infrastructures[$context] = Layer::withName($context . 'Infrastructure')->collectors(
            ClassNameRegexConfig::create('#^' . $context . '\\Infrastructure\\#'),
        );
        $presentations[$context] = Layer::withName($context . 'Presentation')->collectors(
            ClassNameRegexConfig::create('#^' . $context . '\\Presentation\\#'),
        );
    }

    $shared = Layer::withName('Shared')->collectors(
        ClassNameRegexConfig::create('#^Shared\\#'),
    );

    $config->layers(...[
        ...\array_values($domains),
        ...\array_values($applications),
        ...\array_values($infrastructures),
        ...\array_values($presentations),
        $shared,
    ]);

    $rulesets = [];

    foreach ($contexts as $context) {
        // Domain depends on nothing but itself and the shared kernel.
        $rulesets[] = Ruleset::forLayer($domains[$context])->accesses($shared);

        // Application depends on its own Domain, the shared kernel, and
        // other contexts' Application layer (the cross-context port).
        $rulesets[] = Ruleset::forLayer($applications[$context])->accesses(
            $domains[$context],
            $shared,
            ...\array_values(\array_diff_key($applications, [$context => null])),
        );

        // Infrastructure depends on its own Domain and Application, plus
        // the shared kernel - never another context's anything.
        $rulesets[] = Ruleset::forLayer($infrastructures[$context])->accesses(
            $domains[$context],
            $applications[$context],
            $shared,
        );

        // Presentation depends on its own Application (Commands, Queries,
        // Results) and Domain, plus the shared kernel - it never reaches
        // Infrastructure or another context directly.
        $rulesets[] = Ruleset::forLayer($presentations[$context])->accesses(
            $applications[$context],
            $domains[$context],
            $shared,
        );
    }

    $rulesets[] = Ruleset::forLayer($shared);

    $config->rulesets(...$rulesets);
};
