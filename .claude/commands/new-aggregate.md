---
description: Scaffold a new DDD aggregate (Domain + Application + Infrastructure) per the standard.
argument-hint: <Context> <AggregateName>
---

Scaffold a new aggregate following the `php-ddd-clean-architecture` skill. Load that skill first.

Arguments: `$ARGUMENTS` — the bounded context and the aggregate name (e.g. `Catalog Job`).

Create, in `api/src/<Context>/`:

- **`Domain/<Name>/`**
  - `<Name>.php` — aggregate root (`final`, `strict_types`, rich behavior, invariants inside, intention-revealing methods, no setters).
  - `<Name>Id.php` — ID Value Object.
  - any obvious Value Objects for rule-bearing fields.
  - `Exception/` — custom domain exceptions for this aggregate.
  - `<Name>RepositoryInterface.php` — domain-meaningful methods only; returns domain objects.
- **`Application/<Name>/`** — one Command + Handler for the first real use case (handler orchestrates only), and a Result DTO if there's a read.
- **Infrastructure/Persistence/Doctrine/** — `<Name>Entity.php` (persistence model), `<Name>Mapper.php`, and `Doctrine<Name>Repository.php` implementing the interface.

Rules: full implementations, no stubs or `// ...`. `declare(strict_types=1)` and `final`
everywhere. No Symfony/Doctrine in Domain or Application. Ubiquitous language throughout.
Ask me for the aggregate's invariants and first use case before generating, if they're unclear.
