---
name: php-ddd-clean-architecture
description: >
  Authoritative coding standard for this PHP 8.3+/Symfony 7 project: Domain-Driven Design,
  Clean Architecture, SOLID, Clean Code, KISS/DRY/YAGNI, PHP 8 features, error handling,
  and file organization. Load this before writing, reviewing, or discussing ANY class,
  aggregate, value object, use case, repository, command, query, exception, enum, or test.
  Also triggers for "where does this class go", "how should I structure this feature", or any
  architectural/naming decision. Never generate code in this project without it active.
---

# PHP DDD + Clean Architecture — Coding Standard

Stack is **PHP 8.3+ / Symfony 7**. **DDD is mandatory** — the backbone of the project, not decoration. These rules are non-negotiable and CI-enforced (§ Tooling).

## 1. Clean Architecture — The Dependency Rule

```
Domain          ← depends on nothing here, and NOTHING from Symfony/Doctrine
   ↑
Application     ← depends only on Domain
   ↑
Infrastructure  ← depends on Application + Domain, implements their interfaces
   ↑
Presentation    ← depends on Application (Commands, Queries, Results)
```

- Source-code dependencies point **inward only**. Inner layers never know about outer layers.
- **Domain and Application are framework-free.** No `use Symfony\...`, no `use Doctrine\...`, no framework attributes, no `Request`/`Response`, no container access.
- **Dependency Inversion at every boundary:** inner layers define interfaces (ports); Infrastructure implements them (adapters). PostgreSQL, Elasticsearch, RabbitMQ, the LLM, the embedding provider, France Travail — all details behind ports.
- Enforce with **deptrac** in CI (layers + bounded contexts). Machine-checked, not honor-system.

## 2. File & Project Organization

- **One type per file**, file named after it (PSR-4). Never two classes in one file.
- **Prefer many small, well-named files over few large ones.** A big class hides several responsibilities. Class > ~150–200 lines or method > ~20 lines → extract into named collaborators.
- **Screaming architecture** — package by bounded context first, then layer. No top-level `Entity/`, `Service/`, `Repository/`, `Controller/` buckets.
  ```
  src/
    Ingestion/{Domain,Application,Infrastructure}/
    Catalog/{Domain,Application,Infrastructure,Presentation}/
    Search/{Domain,Application,Infrastructure}/
    CandidateProfile/{Domain,Application,Infrastructure,Presentation}/
    Matching/{Domain,Application,Infrastructure,Presentation}/
    Shared/            ← tiny, stable, cross-context kernel only
  ```

## 3. SOLID

- **S** — One class, one reason to change. Parsing a CV and calling the embedding API are two classes.
- **O** — Extend by adding classes (new Command, new Policy), not editing existing domain classes.
- **L** — Every implementation fully honors its interface contract; no surprise exceptions.
- **I** — Small, role-focused interfaces. No God repository.
- **D** — Domain/Application depend on abstractions; Infrastructure implements them. Wire with Symfony DI + autowiring; never call the container from inside a class.

## 4. PHP 8 Features — Use Them

- `declare(strict_types=1);` at the top of **every** file.
- **`final` by default**; extension is a deliberate, documented choice. Composition over inheritance.
- Fully typed — every property, param, return. **Avoid `mixed`.** Use union/intersection types; `never` for always-throwing methods.
- **`readonly`** for Value Objects and DTOs (or `readonly class`). Immutable by default.
- Constructor property promotion for all DTOs/Commands/Queries/Results/VOs.
- **Backed enums** replace status strings and constant sets (`ContractType`, `OfferStatus`, `SeniorityLevel`); enums may carry behavior.
- `match` over `switch`. Named arguments where they aid readability. Nullsafe `?->`, first-class callables `$fn(...)`, new-in-initializer where clearer.
- Typed class constants and `#[\Override]` (8.3). `json_validate()` (8.3) before decoding untrusted LLM/API JSON.
- On 8.4: property hooks / asymmetric visibility over hand-written getters — but never over an intention-revealing domain method.

## 5. Clean Code — Functions, Names, Objects

- Meaningful, pronounceable, searchable names. No `data`, `tmp`, `mgr`, `helper`, `util`, `manager` in the domain.
- Small functions doing one thing at one level of abstraction. Comment-worthy block → extract a named method.
- ≤ 2 args ideal; 3 is a smell; more means a missing object. **No boolean flag args** — split into two methods.
- **Command–Query Separation** — a method does *or* answers, never both.
- **Tell, Don't Ask** — `$offer->expire()`, not read-status-then-set from outside.
- **Law of Demeter** — no `$a->getB()->getC()->do()` train-wrecks.
- No `null` for "not found" in domain reads — throw a domain exception or return an explicit empty/typed-optional.

## 6. Ubiquitous Language

- Use the domain expert's exact words. `match()`, not `compute()`/`process()`/`handle()`.
- **Banned in Domain:** `create`, `update`, `delete`, `manage`, `handle`, `process`, `do`, `execute`. Methods read like sentences: `$profile->matchAgainst($job)`, `$offer->expire()`.
- Exception names tell the whole story: `EmbeddingProviderUnavailable`, `CvTextNotExtractable`, `OfferNoLongerListed`.

## 7. Bounded Contexts

- Each context (Ingestion, Catalog, Search, CandidateProfile, Matching) owns its model and language. Same word may differ across contexts — don't force a shared canonical model.
- Integrate via explicit contracts: an **Anti-Corruption Layer** (France Travail's payload never leaks past Ingestion), or published domain events / application services.
- No context reaches into another's internals; cross-context calls go through Application entry points. deptrac forbids illegal cross-context imports.

## 8. Tactical DDD

- **No anemic models.** Behavior lives with its data. Getters/setters + logic-in-a-service is rejected.
- **Aggregates:** one root; outside code holds only the root; the root enforces invariants and controls child mutation. Only roots have repositories.
- **Entities:** identity via an **ID Value Object** (never a raw string/UUID); equality by identity; construct via constructor or named factory; state changes via intention-revealing methods, never setters.
- **Value Objects:** `readonly`, self-validating in the constructor (throw a domain exception if invalid), equality by value, one concept each. Wrap every rule-bearing primitive (`Money`, `EmailAddress`, `RomeCode`, `SimilarityScore`, `OfferId`).
- **Encapsulate collections** — never expose a mutable internal array. Read-only views out; mutation through the root.
- **Repository interfaces** in Domain, implemented in Infrastructure; domain-meaningful methods only (no `findBy`, no QueryBuilder leak); return domain objects; write methods take the root only.
- **Domain Services:** only when an operation belongs to no single entity/VO; stateless; named `{Action}{Concept}` (`SkillToRomeMapper`), never `XService`.
- **Domain Events:** past tense (`OfferIngested`, `CvProfiled`, `JobMatched`); immutable; raised in the aggregate, dispatched by Application.
- **Application:** one Command/Query per use case (promoted-constructor DTO, no logic); one Handler each (single `__invoke`); handlers **orchestrate only** (fetch → call domain method → persist → dispatch). Any business `if` belongs in the Domain. Read Queries may bypass the domain for performance, returning immutable **Result** DTOs — never entities.
- **Persistence ≠ domain.** The Doctrine entity is a separate Infrastructure class translated by a **Mapper** (structural only). No Doctrine attributes on domain objects.

## 9. Error Handling

- **Every throw is a custom domain exception.** Never throw raw `\Exception`, `\RuntimeException`, `\InvalidArgumentException`, `\DomainException`.
- Name describes what went wrong in ubiquitous language; constructor takes only what the message needs. Group per aggregate under `{Context}/Domain/.../Exception/`.
- **Fail fast** at construction (VOs) and boundaries.
- **Never swallow** exceptions. Catch only to handle or translate.
- Exceptions for exceptional flow, **not control flow** (no-matches-found is a return value).
- **Translate at boundaries:** a 429 from the embedding API, an ES timeout, RabbitMQ down → caught in the adapter, re-thrown as a domain exception. The domain never sees an HTTP status or a Doctrine exception.
- **LLM/API output is untrusted:** `json_validate()` then schema-validate hard before it enters the domain; malformed → domain exception, never a half-populated profile.

## 10. KISS · DRY · YAGNI

- **KISS** — simplest design that correctly models the domain. No pattern for its own sake.
- **DRY is about knowledge, not text** — look-alike code that changes for different reasons is not duplication; don't couple it.
- **YAGNI** — build only the current use case. The DB-queue→RabbitMQ transport seam is allowed only because it's a real planned phase.

## 11. Tests & Tooling

- Tests are first-class code and follow every rule here. PHPUnit; one logical assertion per test; behavior-named tests (`expiredOffersAreRemovedOnSync`). Domain + Application unit-testable with zero infrastructure. Test through public API; mock only ports, never your own domain logic.
- No magic numbers/strings — enums, typed constants, VOs.
- CI gates (zero baseline on new code): **PHPStan max (level 9)**, **PHP-CS-Fixer** (PER-CS/PSR-12), **deptrac** (layers + contexts), **Infection** (mutation testing on Domain). Optional **Rector** for idiom upkeep.

## Checklist Before Committing Any Class

1. Right context + layer; depends only inward; no framework in Domain/Application?
2. One type per file, named after it, in the right context package?
3. Small and single-responsibility — not two things hiding together?
4. `strict_types`, `final`, fully typed, `readonly` where immutable, no `mixed`?
5. Ubiquitous language; no CRUD/tech verbs in Domain?
6. Rule-bearing primitives wrapped in VOs; collections encapsulated?
7. Rich behavior (not anemic); Tell-Don't-Ask; no Demeter train-wrecks?
8. Every throw a custom domain exception; nothing swallowed?
9. Each function one thing, ≤ 2 args, no flag args, command *or* query?
10. Full implementation — no stubs, no `// ...`, no TODOs — would a domain expert recognize their language?
