---
description: Everyday code-hygiene rules — comments, dead code, docblocks, naming. Applies to all PHP.
---

# Code Hygiene

## Comments — only when necessary

- **Default to no comment.** Well-named methods and Value Objects are the documentation. If you reach for a comment to explain _what_ code does, rename or extract instead.
- A comment is allowed **only to explain _why_** when the reason is non-obvious (a workaround, a regulatory constraint, a deliberate trade-off). Example worth keeping: `// France Travail licence forbids retaining recruiter contact data after an offer is delisted`.
- **No comments that restate the code** (`// increment counter`), no section-divider banners, no changelog/author comments (git owns history).

## Docblocks

- No docblock that merely repeats a typed signature — the types already say it.
- Add a docblock **only when it carries information the type system can't**: array shapes / generics for PHPStan (`@return list<SkillResult>`), `@throws` for a domain exception, or a genuinely non-obvious `@param` constraint.

## Dead code & leftovers

- **Delete** unused code, don't comment it out. Git remembers it.
- **No `TODO` / `FIXME` committed.** If it matters, open an issue and reference it; if it doesn't, do it now or drop it.
- No debug artifacts: `dd()`, `dump()`, `var_dump()`, `error_log()`, stray `echo`.

## Naming

- Names are self-documenting, intention-revealing, and use the ubiquitous language (see the coding standard).
- No `data`, `info`, `tmp`, `obj`, `helper`, `util`, `manager`, `handler` (outside the Command/Query handler pattern) in domain names.
- Boolean names read as assertions: `isExpired`, `hasEmbedding`, `requiresDegree`.

## Formatting

- Formatting is the formatter's job (PHP-CS-Fixer). Never hand-format or argue style in review — run the tool.
