---
description: Git workflow and commit message rules. Applies to every commit and branch.
---

# Git & Commits

## Commit messages — short but clear

- Format: `type(context): imperative summary` — e.g. `feat(ingestion): expire offers absent from sync`.
- **Summary ≤ 50 chars**, imperative mood ("add", not "added"/"adds"), lowercase, no trailing period.
- `type` ∈ `feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `perf`, `build`, `ci`.
- `context` is the bounded context or area: `ingestion`, `catalog`, `search`, `profile`, `matching`, `shared`, `infra`.
- **Body only when the *why* isn't obvious** from the summary. Wrap at 72 chars. Explain intent and trade-offs, never restate the diff.
- Reference an issue when one exists: `Refs #42` / `Closes #42`.

## Banned commit messages

- No `wip`, `fix`, `stuff`, `update`, `changes`, `misc`, `.`, or anything a reviewer can't understand without the diff.

## Commit hygiene

- **One logical change per commit.** Don't mix a refactor with a behavior change. If you can't describe it in one line, it's more than one commit.
- Commit compiles, passes PHPStan max, and passes tests. No red commits on the main branch.
- No commented-out code, debug dumps (`dd()`, `var_dump`), or secrets in a commit.

## Branches & PRs

- Branch names: `type/short-slug` — `feat/hybrid-search`, `fix/token-refresh`.
- Keep PRs small and single-purpose; a reviewer should hold the whole change in their head.
- PR description states **what** changed and **why**, and how it was verified. Link the issue.

## Examples

```
feat(matching): explain skill gaps grounded in ROME codes
fix(ingestion): refresh France Travail token before expiry
refactor(catalog): extract OfferStatus into a backed enum
test(search): cover RRF fusion of BM25 and kNN results
```
