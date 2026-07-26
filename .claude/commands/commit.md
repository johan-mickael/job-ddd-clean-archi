---
description: Stage sensible changes and write a short, clear conventional commit message.
---

Create a git commit for the current changes, following `.claude/rules/git-and-commits.md`.

Steps:

1. Run `git status` and `git diff` (and `git diff --staged`) to see what changed.
2. If the changes are more than one logical change, **split into multiple commits** — stage and commit each separately.
3. For each commit, write a message as `type(context): imperative summary`:
   - summary ≤ 50 chars, imperative mood, lowercase, no trailing period;
   - `type` ∈ feat|fix|refactor|test|docs|chore|perf|build|ci;
   - `context` = bounded context or area (ingestion, catalog, search, profile, matching, shared, infra);
   - add a wrapped body **only** if the _why_ isn't obvious; never restate the diff;
   - reference an issue if one applies (`Refs #N` / `Closes #N`).
4. Never commit debug dumps, commented-out code, secrets, or a red build. If PHPStan/tests aren't passing, say so and stop.
5. Show the final message(s) before committing.

Do not push unless explicitly asked.
