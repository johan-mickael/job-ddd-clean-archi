---
name: france-travail-integration
description: >
  How to integrate the France Travail (ex-Pôle emploi) job APIs: the Offres d'emploi API,
  OAuth2 client-credentials auth, the ROME occupation/competency referential, and the
  binding reuse-license obligations (24h sync, deletion propagation, anonymization, full
  display, no paywall). Load this whenever working on the Ingestion context, the France
  Travail client/adapter, the anti-corruption mapping, offer expiry, or skill-to-ROME mapping.
---

# France Travail Integration

France Travail is the ex-Pôle emploi (rebranded Jan 2024). Developer platform: **francetravail.io**.

## Offres d'emploi API

- Free/open access; requires a francetravail.io account and accepting the reuse license.
- Auth: **OAuth2 client-credentials** (client ID + secret → bearer token). Implement the client as an Infrastructure adapter behind a domain port, with **token caching** (do not re-auth per request; refresh before expiry).
- Endpoint: `GET https://api.francetravail.io/partenaire/offresdemploi/v2/offres`
- Query params include `motsCles`, `commune`, `rayon`, plus filters on occupations, communes, départements, contract types. Paginated.
- Returns active offers with title, location, company, contract, description, apply URL, ROME code, and referential data.

## ROME referential API

- Official French occupation + competency taxonomy. **Use it to ground skill matching** — map CV skills and job requirements to ROME competency codes instead of inventing a taxonomy or trusting the LLM alone.
- Wire it into the **Matching** context; model `RomeCode` as a Value Object.

## License compliance — HARD RULES (contractual)

> Drafted from reproductions of the license + France Travail CGU — **not legal advice**. Verify against the authoritative license on francetravail.io before shipping.

1. **Sync ≥ every 24h** and propagate created/deleted/modified content. The scheduler floor is 24h.
2. **Propagate deletions** every sync — offers absent upstream are expired/removed. This is a compliance use case, not just UX. Never show dead listings.
3. **Anonymize on retention** — content deleted upstream may only be retained if the recruiter's name, first name, and contact details are removed. → **Simplest compliant design: never persist personal-contact fields at all.** Strip them at the anti-corruption layer on ingest. Not needed for matching; also shrinks GDPR surface.
4. **Full display + logo** — the job **detail** view must render the entirety of the API content for that offer, including the company logo. (Search-result summaries may be partial.)
5. **No paywall** on the listings — access to France Travail offers must be free. You may charge for value-added features, never for the underlying public listings.
6. **No resale / no advertising use**; no building application files for non-recruitment purposes.

What stays yours: your code, embeddings, and UI. The license rides along only with the France Travail data.

## Architecture rules for this context

- France Travail's payload shape **never leaks past Ingestion**. An Anti-Corruption Layer maps it to our `Job`/`Offer` domain model.
- Model the domain around **our** concepts (skills, seniority, contract type, ROME code), not FT's raw fields.
- Upsert keyed on FT's stable offer ID; re-embed only when the description actually changed.
- Always deep-link the job detail to the live France Travail posting (our copy may lag).
