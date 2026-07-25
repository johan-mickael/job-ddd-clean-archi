---
description: Review current changes against the DDD/Clean Architecture standard.
---

Review the current uncommitted changes against this project's coding standard.

Delegate to the `code-reviewer` subagent. If it isn't available, load the
`php-ddd-clean-architecture` skill and perform the review yourself using its checklist.

Focus on the diff only (`git diff` and any staged changes), report hard-rule violations
first, and give concrete `file:line → problem → fix` items. Don't rewrite everything —
point precisely so the author fixes it.
