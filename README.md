# Semantic Job Search & CV Matching

Portfolio-grade DDD/Clean Architecture app matching CVs to France Travail job
offers via hybrid (BM25 + kNN) search. Full product/architecture rules live in
[`CLAUDE.md`](CLAUDE.md); the PHP coding standard lives in
[`.claude/skills/php-ddd-clean-architecture`](.claude/skills/php-ddd-clean-architecture/SKILL.md).

## Layout

```
api/    Symfony 7 / FrankenPHP backend — each bounded context (Catalog, Search,
        Ingestion, CandidateProfile, Matching, Shared) is its own top-level
        PSR-4 namespace, Domain/Application/Infrastructure/Presentation
        nested inside (see the coding-standard skill). See api/README.md for
        the FrankenPHP/Docker base image itself.
web/    Vue 3 + Vite + Tailwind SPA, talks to api/ over HTTP.
```

`compose.yaml` (+ `compose.override.yaml` for dev, `compose.prod.yaml` for
prod) orchestrates the stack from the repo root; the `php` service builds
from `./api`. Postgres, Elasticsearch, and a `web` service are added as later
build phases reach them (see `CLAUDE.md` §10) — don't wire them ahead of the
phase that needs them.

## Getting started

```
docker compose build --no-cache
docker compose up -d
```

First boot installs the Symfony skeleton into `api/` automatically (see
`api/frankenphp/docker-entrypoint.sh`).

```
cd web && npm install && npm run dev
```
# job-ddd-clean-archi
