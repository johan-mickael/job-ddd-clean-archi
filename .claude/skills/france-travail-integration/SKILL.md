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
- Rate limit: sources disagree (3 req/s per an older client library, 10 req/s per data.gouv.fr) — treat 3 req/s as the safe assumption and always retry-with-backoff on `429`/`502` regardless.

### Verified endpoints and auth (cross-checked against the archived official doc + a live, working 2024-era integration — not just recollection)

- **Token:** `POST https://entreprise.francetravail.fr/connexion/oauth2/access_token` with query param `realm=/partenaire` and form body `grant_type=client_credentials&client_id=...&client_secret=...&scope=...`.
  - **Scope is not free-form:** `api_offresdemploiv2 o2dsoffre application_{client_id}` — the client's own id is echoed back inside the scope string. Getting this wrong fails auth silently-ish (generic `invalid_client`).
- **Search (paginated list):** `GET https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search` — **not** `/offres` (that path doesn't exist; `/search` is the actual list endpoint).
- **Detail (single offer):** `GET https://api.francetravail.io/partenaire/offresdemploi/v2/offres/{offerId}` — returns **204 No Content** (not 404) when the id doesn't exist.
- Historical/legacy base domain in older docs: `api.emploi-store.fr` — same paths, current domain is `api.francetravail.io`.

### Search query parameters (verified, not exhaustive of every referential filter but complete for the documented ones)

| Param | Cardinality | Notes |
|---|---|---|
| `range` | 0..1 | Pagination: `first-last`, e.g. `range=0-49`. First index ≤ 1000, last index ≤ 1149, span ≤ 150 per call. Up to 1150 offers reachable per distinct search by paging; narrow with dates if more exist. |
| `sort` | 0..1 | `0`=pertinence↓/distance↑/date↓ (default), `1`=date↓ first, `2`=distance↑ first. |
| `motsCles` | 0..7 | Free-text; matches `intitule`, `romeCode`, `romeLibelle`, `competences.libelle`, `formations.domaineLibelle`, `permis.libelle`, `langues.libelle`, and `description` (if matched via title/ROME libelle). |
| `domaine`, `codeROME` (0..3), `theme`, `appellation`, `secteurActivite` (0..2, comma-sep NAF codes) | — | Referential-code filters; always send the **code**, never the label. |
| `experience` | 0..1 | `1`=<1yr, `2`=1-3yr, `3`=>3yr. |
| `experienceExigence` | 0..1 | `D`=débutant accepté, `S`=souhaitée, `E`=exigée. |
| `typeContrat` | 0..10 | Comma-sep codes (`CDI`,`CDD`,`MIS`,`LIB`,...). OR'd with `natureContrat` when both given — read the doc's remark before combining. |
| `natureContrat` | 0..n | Comma-sep codes (`E1`,`NS`,...). |
| `origineOffre` | 0..1 | `1`=France Travail, `2`=Partenaire. |
| `qualification` | 0..1 | `0`=non cadre, `9`=cadre (search-filter granularity; detail response has a finer 1-9 `qualificationCode`, see below). |
| `tempsPlein` | 0..1 | boolean. |
| `commune` (0..5, INSEE code) + `distance` (default 10, `0`=exact commune only) | — | Paris/Lyon have special-cased radius behavior per the doc. |
| `departement` (0..5) + `inclureLimitrophes` | — | |
| `region`, `paysContinent`, `niveauFormation`, `permis` | 0..1 | Referential codes. |
| `salaireMin` + `periodeSalaire` (`M`/`A`/`H`/`C`) | 0..1 | Both or neither. |
| `accesTravailleurHandicape`, `offresMRS` | 0..1 | booleans. |
| `grandDomaine` | 0..n | Big letter-coded domain buckets (A=Agriculture...N=Transport/Logistique). |
| `publieeDepuis` | 0..1 | days: `1`, `3`, `7`, `14`, `31`. |
| `minCreationDate` + `maxCreationDate` | 0..1 | ISO-8601 `YYYY-MM-DDTHH:MM:SSZ`, both required together. This pair is the intended way to page past the 1150-result ceiling. |
| `modeSelectionPartenaires` + `partenaires` (0..20) | — | Include/exclude specific partner sources. |

### Response shape

- Success (fits in one page): `200`. Success but more results than page size / >150: `206 Partial`.
- Headers: `Content-Range: offres {first}-{last}/{total}`, `Accept-Range: offres {max_per_call}`.
- Body: `{ "resultats": [Offre, ...], "filtresPossibles": [{ "filtre": "typeContrat", "agregation": [{ "valeurPossible": "CDI", "nbResultats": 138 }, ...] }, ...] }`.
- Errors are JSON: `{ "codeErreur": "...", "codeHttp": 400, "message": "..." }` — `message` is human-readable French, safe to log but not to show a user verbatim.

### `Offre` object — full verified field list (the schema our anti-corruption layer must decide what to keep from)

Top-level: `id` (7-char, also derivable into `https://candidat.francetravail.fr/offres/recherche/detail/{id}`), `intitule`, `description`, `dateCreation`, `dateActualisation`, `romeCode`, `romeLibelle`, `appellationLibelle`, `typeContrat` (required), `typeContratLibelle` (required), `natureContrat`, `experienceExige` (`D`/`E`/`S`), `experienceLibelle`, `experienceCommentaire`, `outilsBureautiques`, `dureeTravailLibelle`, `complementExercice`, `conditionExercice`, `alternance` (bool), `nombrePostes`, `accessibleTH` (bool), `deplacementCode`, `deplacementLibelle`, `qualificationCode` (`1`..`9`, Manœuvre→Cadre), `qualificationLibelle`, `secteurActivite` (NAF code), `secteurActiviteLibelle`, `trancheEffectifEtab`.

Nested:
- `lieuTravail`: `libelle`, `latitude`, `longitude`, `codepostal`, `commune` (INSEE code).
- `entreprise`: `nom`, `description`, `logo` (URL), `url`.
- `origineOffre` (required): `origine` (`1`=FT,`2`=Partenaire), `urlOrigine` (**the required deep-link target**, §7.4/escape hatch), `partenaires[]` (`nom`,`url`,`logo`).
- `formations[]`: `domaineLibelle`, `niveauLibelle`, `commentaire`, `exigence` (`E`/`S`).
- `langues[]`, `permis[]`: `libelle`, `exigence` (`E`/`S`).
- `competences[]`: `code`, `libelle`, `exigence` (`E`/`S`) — **this is the field to map to ROME competency codes for Matching.**
- `salaire`: `libelle`, `commentaire`, `complement1`, `complement2`.
- `qualitesProfessionnelles[]`: `libelle`, `description`.
- **`contact`** (personal-contact block — see compliance rule 3 below): `nom`, `coordonnees1`, `coordonnees2`, `coordonnees3`, `telephone`, `courriel`, `commentaire`, `urlRecruteur`, `urlPostulation`.
- **`agence`** (also contact-shaped, same treatment as `contact`): `telephone`, `courriel`.

`contact.*` and `agence.*` are the fields that must never reach past the anti-corruption layer — not just `contact.nom`. `contact.urlPostulation` looks tempting as an "apply" link but is the recruiter's own form; the license-mandated deep-link is `origineOffre.urlOrigine`, not this.

## ROME referential API

- Official French occupation + competency taxonomy. **Use it to ground skill matching** — map CV skills and job requirements to ROME competency codes instead of inventing a taxonomy or trusting the LLM alone.
- Wire it into the **Matching** context; model `RomeCode` as a Value Object.
- Available `référentiel` collections (via `GET .../offresdemploi/v2/referentiel/{nom}`, no params): `domaine`, `appellations`, `metiers`, `themes`, `continents`, `pays`, `regions`, `departements`, `communes`, `secteursActivites`, `naturesContrats`, `typesContrats`, `niveauxFormations`, `permis`, `langues`. Each returns `[{ "code": "...", "libelle": "..." }, ...]`.

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
