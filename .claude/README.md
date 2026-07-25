# `.claude/` — project configuration for Claude Code

Drop this folder, plus the root `CLAUDE.md`, into your repository root and commit it. Everyone on the project then shares the same rules, skills, commands, and permissions.

## Layout

```
CLAUDE.md                              ← project brief; loaded every session (at repo root)
.claude/
  settings.json                        ← team permissions (committed)
  settings.local.json.example          ← copy to settings.local.json for personal overrides (gitignored)
  README.md                            ← this file
  rules/
    git-and-commits.md                 ← short, clear conventional-commit rules
    code-hygiene.md                    ← comments only when needed, no dead code, naming
  skills/                              ← auto-loaded when relevant
    php-ddd-clean-architecture/        ← the authoritative coding standard (loads for any code work)
    france-travail-integration/        ← FT API, OAuth, ROME, license (loads for ingestion work)
    elasticsearch-hybrid-search/       ← ES mapping, embeddings, RRF, no-ELSER (loads for search work)
  agents/
    code-reviewer.md                   ← subagent that reviews a diff against the standard
  commands/
    commit.md                          ← /commit  — stage + write a clean commit message
    review.md                          ← /review  — review current changes
    new-aggregate.md                   ← /new-aggregate <Context> <Name> — scaffold an aggregate
```

## How the pieces fit

- **`CLAUDE.md`** holds the always-on project brief (what to build, stack, architecture, contexts, license constraints, build phases). It imports the rule files so they're always in effect.
- **Skills** hold deep knowledge that's only needed for specific work, so they load on demand instead of bloating every session. The coding standard is a skill because it should activate precisely when code is being written or reviewed.
- **Rules** are small, always-applied instruction files split by concern.
- **Commands** are manual `/` actions; **agents** are subagents with their own context (the reviewer runs isolated).

## First-time setup checklist

1. Ensure `.env`, `.env.local`, and `config/secrets/**` are gitignored (settings.json also denies reading them).
2. Add `.claude/settings.local.json` to `.gitignore`.
3. Install the dev tools referenced by the standard: `phpunit`, `phpstan`, `php-cs-fixer`, `deptrac`, `infection`.
4. Adjust `settings.json` permissions to match your actual scripts.
