---
name: elasticsearch-hybrid-search
description: >
  How this project does semantic + keyword (hybrid) job search in Elasticsearch: index
  mapping with a dense_vector field, our own embeddings (NOT ELSER on the free license),
  BM25 + kNN fused with RRF computed in PHP (NOT ES's retriever.rrf, which is
  Platinum-licensed), and the rule that Elasticsearch is a derived, rebuildable index. Load
  whenever working on the Search context, ES mappings, the indexing projection, embeddings,
  or query building.
---

# Elasticsearch Hybrid Search

## Core rules

- **Elasticsearch is a derived, disposable index**, built from PostgreSQL (the system of record). You must be able to drop it and rebuild fully from Postgres — provide a re-index use case. This protects you when the embedding model or mapping changes.
- **No ELSER, and no `retriever.rrf` either.** On the free self-hosted Basic license, ELSER is unavailable, and so is Elasticsearch's own `retriever.rrf` framework — it requires a Platinum+ license and 403s ("current license is non-compliant") even under `xpack.license.self_generated.type: basic`. We generate **our own embeddings**, store them in a `dense_vector` field, and fuse BM25 + kNN with the RRF **formula** ourselves in PHP (`Search\Infrastructure\Elasticsearch\ReciprocalRankFusion`) over two plain, license-unrestricted searches.
- Access ES only from the **Search** context's Infrastructure, behind a domain-defined search port. No ES client types in Domain/Application.
- Client: `elasticsearch-php`.

## Embeddings

- Hosted **multilingual** model (must handle French) — e.g. OpenAI `text-embedding-3-large`, Cohere `embed-multilingual`, or Voyage. Behind a port so it's swappable.
- Generate at ingest; store the vector on the ES document. **Re-embed only when the job description changed** — embeddings cost time and money.
- Model the vector dimension as config; the mapping's `dims` must match the chosen model.

## Why hybrid (not pure semantic)

- Pure vector search misses exact tokens — specific skills, certifications, product names. BM25 nails those.
- BM25 alone misses paraphrase and natural-language queries. Semantic nails those.
- **Hybrid (BM25 + kNN, fused with RRF) is the default.** Do not ship pure-semantic.

## Shape

- Index doc: a denormalized, searchable projection of the `Job` aggregate (title, description, location, contract, ROME code, salary, remote flag, apply URL) **plus** the `dense_vector`. No personal-contact fields (license).
- Query: two plain searches - a `multi_match` query (lexical, BM25) and a top-level `knn` query (semantic) - each returning a ranked window of IDs, merged into one ranked list by `ReciprocalRankFusion` in PHP (not ES's `retriever.rrf`, which 403s on Basic). Apply shared filters (location, contract, remote) to both queries.
- Natural-language search: the LLM turns the user's phrase into structured filters + a semantic query, which this context executes. The LLM never talks to ES directly.

## Match explanation (RAG)

- The only place RAG belongs. Retrieve the job + candidate profile, then have the LLM explain skill-by-skill overlap and honestly flag gaps, grounded in ROME codes and the actual job text. It must not invent requirements absent from the source.
