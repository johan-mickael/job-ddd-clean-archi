# CLAUDE.md — Semantic Job Search & CV Matching

> Project instructions for building this application. Read this file fully before writing any code.
> This file describes **what** to build and the **product/architecture constraints**.
> Coding rules live in the `php-ddd-clean-architecture` skill; git and hygiene rules are imported just below.

**Always-applied rules (imported):**
@.claude/rules/git-and-commits.md
@.claude/rules/code-hygiene.md

---

## 1. Project Goal

Build a working web application that:

1. Ingests French job offers from the **France Travail** public API into our own storage.
2. Lets a user **upload a CV**, extracts a structured profile from it, and **matches it to relevant jobs** using semantic + keyword (hybrid) search.
3. Supports **natural-language search** ("remote backend roles that don't require a degree").
4. Explains **why** a given job matches the user's CV, grounded in real data (not hallucinated).

**Primary purpose:** a portfolio-grade demonstration of clean code, sound architecture, and a genuinely useful working product. Novelty is not the goal — engineering quality is. The author may also use it personally for their own job search.

**Success looks like:** a reviewer reading the codebase concludes the author is a senior-level engineer, and the app actually returns good matches.

---

## 2. Coding Standard — lives in the skill

**Stack: PHP 8.3+ / Symfony 7. DDD is mandatory** — the backbone of the project, not decoration.

The full, authoritative coding standard lives in the auto-loaded skill
**`.claude/skills/php-ddd-clean-architecture`** — Clean Architecture, SOLID, Clean Code,
PHP 8 features, tactical DDD, error handling, file organization, and tooling. It activates
automatically whenever code is written or reviewed. Everyday hygiene and git conventions live
in `.claude/rules/`. Don't restate those rules here — edit them at the source.

Non-negotiable headlines (the skill carries the detail):

- **Dependency rule:** Domain ← Application ← Infrastructure ← Presentation. Domain and Application are **framework-free** (no Symfony/Doctrine imports). Enforced by deptrac.
- **Small files, one type per file, screaming architecture** — package by bounded context, then layer.
- **PHP 8:** `declare(strict_types=1)`, `final` by default, fully typed (no `mixed`), `readonly` Value Objects, backed enums, `match`.
- **Rich DDD:** no anemic models; aggregates enforce their invariants; IDs and rule-bearing primitives are Value Objects; persistence entity ≠ domain object (Mapper between them).
- **Errors:** only custom domain exceptions; never swallowed; infrastructure failures translated at the boundary; untrusted LLM/API JSON validated before it enters the domain.
- **Ubiquitous language** everywhere; no CRUD/tech verbs in the Domain.
- **CI gates:** PHPStan max, PHP-CS-Fixer, deptrac, Infection.

---

## 3. Tech Stack (decided — do not re-litigate without reason)

| Concern                                     | Choice                                                                                                       | Notes                                                                                                                                      |
| ------------------------------------------- | ------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------ |
| Language / framework                        | **PHP 8.3+ / Symfony 7**                                                                                     | The clean-code showcase.                                                                                                                   |
| System of record                            | **PostgreSQL** (Doctrine)                                                                                    | Durable truth: raw + normalized jobs, users, CVs, match history.                                                                           |
| Search index                                | **Elasticsearch** (`elasticsearch-php`)                                                                      | Derived, disposable, rebuildable from Postgres.                                                                                            |
| Async / scheduling                          | **Symfony Messenger + Scheduler**                                                                            | Ingestion, embedding, indexing as messages.                                                                                                |
| CV text extraction                          | `smalot/pdfparser` or `pdftotext` (PDF), `PhpOffice/PhpWord` (DOCX)                                          | Extract raw text only.                                                                                                                     |
| Structured extraction                       | **Hosted LLM** (Anthropic/OpenAI) → strict JSON                                                              | No hand-built skill dictionary.                                                                                                            |
| Embeddings                                  | **Hosted multilingual model** (e.g. OpenAI `text-embedding-3-large`, Cohere `embed-multilingual`, or Voyage) | Must handle French. Stored as `dense_vector`.                                                                                              |
| Natural-language search + match explanation | **Hosted LLM**                                                                                               | See §7 and §8.                                                                                                                             |
| Frontend                                    | **Vue or React + Tailwind + shadcn/ui** (decoupled SPA over a Symfony API)                                   | Use the `frontend-design` skill so it doesn't look templated. A Symfony UX/Twig monolith is an acceptable fallback if scope needs cutting. |

**Do NOT add** LangChain/LlamaIndex-style orchestration frameworks. The two or three LLM calls are hand-rolled as clean Application-layer services — this reads better and stays explainable.

**Elasticsearch licensing constraint:** on the free self-hosted Basic license, **ELSER is unavailable**, and so is the **`retriever.rrf` framework** (verified: it 403s with "current license is non-compliant" even under `xpack.license.self_generated.type: basic`). We generate our own embeddings, store them in a `dense_vector` field, and run BM25 (`multi_match`) and kNN as two plain Basic-legal searches, fusing their rankings with the RRF *formula* ourselves in PHP (`Search\Infrastructure\Elasticsearch\ReciprocalRankFusion`) rather than via ES's retriever API. Do not design around ELSER, and do not reach for `retriever.rrf`.

---

## 4. Architecture Overview

```text
France Travail API
      │  (scheduled, ≥ every 24h — license floor)
      ▼
[ Ingestion ]  OAuth2 client-credentials + token caching
      │        Anti-corruption layer: FT payload → our domain model
      ▼
PostgreSQL  ── system of record (normalized Job aggregate, no personal-contact fields)
      │
      │  (on create/change) enqueue embed job
      ▼
[ Embedding ]  description → vector (hosted model)
      │
      ▼
Elasticsearch  ── derived search index (denormalized doc + dense_vector)
      │
      ▼
[ Search / Serve ]  hybrid BM25 + kNN (RRF) → Presentation → SPA
```

**Two stores, distinct jobs.** Postgres is the repository behind the domain. Elasticsearch is an outbound search **port**. You must be able to drop Elasticsearch and fully rebuild it from Postgres (re-index use case). This protects you when the embedding model or ES mapping changes.

**The apply link is the escape hatch.** Our copy can lag; always deep-link to the live France Travail posting on the job detail view.

---

## 5. Bounded Contexts

Model these as separate contexts under the coding standard. Do not let France Travail's payload shape leak past ingestion.

- **Job Ingestion** — talks to France Travail, owns the anti-corruption mapping, sync scheduling, and expiry reconciliation. Its job is to hand clean domain `Job` aggregates to the rest of the system.
- **Job Catalog** — the `Job` aggregate as the rest of the app knows it (Postgres). Domain-meaningful, framework-free.
- **Search** — indexing projection + hybrid query. Elasticsearch lives entirely here, behind a domain-defined search port.
- **Candidate Profile** — CV upload, text extraction, structured profile (skills, experience, seniority, domains). Deliberately **omits** France Travail personal-contact data.
- **Matching** — given a profile, retrieve + rank jobs, and produce the grounded "why this matches" explanation (the only place RAG belongs).

Model the domain around **our** concepts (skills, seniority, contract type, ROME code), never around France Travail's raw response fields.

---

## 6. Data Source — France Travail

Platform: **francetravail.io** (France Travail = ex-Pôle emploi since Jan 2024).

- **Offres d'emploi API** — primary job source. Free/open access; requires an account and accepting the reuse license; OAuth2 client-credentials (client ID + secret).
  - Endpoint: `GET https://api.francetravail.io/partenaire/offresdemploi/v2/offres`
  - Query params include `motsCles`, `commune`, `rayon`, plus filters on occupations, communes, départements, contract types. Paginated.
- **ROME referential API** — official French occupation + competency taxonomy. **Use this to ground skill matching** (map CV skills and job requirements to ROME competency codes) instead of inventing a taxonomy or trusting the LLM alone. This is a key differentiator; wire it into the Matching context.

Implement the OAuth2 client as an Infrastructure service with **token caching** (don't re-auth per request). Expose it to the domain via an interface.

---

## 7. License Compliance — HARD RULES

> These are contractual obligations from the France Travail reuse license, not preferences. Treat violations as build-blocking bugs.
> ⚠️ This summary was drafted from reproductions of the license + France Travail's CGU, **not legal advice**. Before shipping, verify against the authoritative license on francetravail.io (via the "Utiliser l'API" flow).

1. **Sync at least every 24 hours.** The reuser must call the API ≥ once per 24h and propagate created/deleted/modified content accordingly. The scheduler's floor is 24h — build it in.
2. **Propagate deletions.** Offers removed upstream must be removed (or expired) in our store every sync. Expiry reconciliation is a **compliance use case**, not just UX. Never show dead listings.
3. **Anonymize on retention.** Retained content that has been deleted upstream must be anonymized by removing the recruiter's name, first name, and contact details.
   → **Simplest compliant design: never persist the personal-contact fields at all.** We don't need them for matching, and this also shrinks our GDPR surface. Strip them at the anti-corruption layer on ingest.
4. **Full display + logo.** When rendering a job **detail** view, show the entirety of the content provided by the API for that offer, **including the company logo**. (Search-results summaries may be partial; the detail page may not.)
5. **No paywall on the listings.** Access to the France Travail offers must be free. You may charge for value-added features you build (matching, CV tools) — never gate the underlying public listings.
6. **No resale / no advertising use** of the data, and no building application files for purposes other than recruitment.

What stays ours: our code, embeddings pipeline, and UI. The license rides along only with the France Travail data itself.

---

## 8. CV Processing

1. Accept PDF / DOC / DOCX upload.
2. Extract **raw text** (`pdftotext`/`smalot`, `PhpWord`). No parsing logic here — text out.
3. Send text to the LLM with a **strict JSON schema**: skills (with rough years each), roles, seniority, domains, languages.
4. **Validate the JSON hard** — this is a Value Object / domain-validation showcase. Reject/repair malformed output; never trust raw LLM JSON downstream.
5. Map validated data to a `CandidateProfile` domain object. Optionally enrich skills → ROME competency codes here.

Do not build or maintain a skill keyword dictionary. The LLM + ROME referential replace it.

---

## 9. Search & Matching

- **Hybrid retrieval:** BM25 (lexical) + kNN over `dense_vector` (semantic), fused with **RRF** (application-level — see §3's licensing note). Pure semantic alone misses exact tokens (specific skills, certs); pure lexical misses paraphrase. Hybrid is the default.
- **Own embeddings:** generate at ingest, store in ES. Re-embed only when the job description actually changed (embeddings cost time/money — never regenerate blindly).
- **Natural-language search:** LLM turns the user's phrase into structured filters + a semantic query, which the Search context executes.
- **Match explanation (the only RAG feature):** retrieve job + candidate profile → LLM explains skill-by-skill overlap and **honestly flags gaps** ("missing Kubernetes, required by most matches"), grounded in ROME codes and the actual job text. Must not invent requirements not present in the source.

---

## 10. Build Phases (MVP-first)

Ship each phase working before starting the next. No half-built horizontal layers.

- **Phase 0 — Skeleton.** Symfony + Postgres + Docker (ES, Postgres). CI, static analysis (PHPStan max), tests wired. Enforce the layer boundaries with a dependency checker (e.g. deptrac).
- **Phase 1 — Ingestion.** OAuth2 client + token cache. Anti-corruption mapping → `Job` aggregate (no personal-contact fields). Upsert into Postgres keyed on FT offer ID. 24h scheduler. Expiry reconciliation. This is pure backend and fully testable.
- **Phase 2 — Search.** ES mapping (text + dense_vector). Embedding at ingest. Index projection from Postgres. Hybrid RRF query. Plain search UI. **This alone is already a working semantic job search — ship it.**
- **Phase 3 — CV matching.** Upload → extract → LLM structured profile → validate → `CandidateProfile`. Match profile against ES. Results UI.
- **Phase 4 — Intelligence.** Natural-language query understanding + the grounded "explain the match" RAG feature. ROME grounding.
- **Phase 5 — Polish.** Design pass with the `frontend-design` skill, empty/error states, accessibility, README with architecture diagram + decision log.

---

## 11. Configuration / Secrets

Never commit secrets. Use Symfony's env/secrets vault. Expected config:

- `FT_CLIENT_ID`, `FT_CLIENT_SECRET` — France Travail OAuth2.
- `LLM_API_KEY`, `LLM_MODEL` — structured extraction, NL search, explanations.
- `EMBEDDING_API_KEY`, `EMBEDDING_MODEL` — multilingual embeddings.
- `ELASTICSEARCH_DSN`, `DATABASE_URL`.

Wrap every external provider behind a domain-defined interface so models can be swapped without touching Application/Domain.

---

## 12. Non-Negotiables (quick checklist)

- [ ] Coding standard (`ddd-clean-architecture`) followed for every class.
- [ ] France Travail payload never leaks past the ingestion anti-corruption layer.
- [ ] Personal-contact fields never persisted.
- [ ] Sync runs ≥ every 24h and propagates deletions.
- [ ] Job detail view shows full content + logo; listings not paywalled.
- [ ] Elasticsearch is rebuildable from Postgres; no ELSER dependency.
- [ ] LLM JSON is validated before it enters the domain.
- [ ] Match explanations are grounded — no invented requirements.
- [ ] Every external provider sits behind an interface.
- [ ] Each phase ships working before the next begins.

---

## 13. Things NOT To Do

- Don't proxy live user searches to France Travail (tiny quotas; also the wrong architecture). Serve from our stores.
- Don't add an orchestration framework for a handful of LLM calls.
- Don't design around ELSER.
- Don't build a skill dictionary.
- Don't store or display recruiter personal-contact data.
- Don't paywall the public listings or use the data for ads/resale.
- Don't build all layers horizontally — build vertically, phase by phase, each one working.
