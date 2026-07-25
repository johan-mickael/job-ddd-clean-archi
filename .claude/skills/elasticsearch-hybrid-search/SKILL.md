---
name: elasticsearch-hybrid-search
description: >
  How this project does semantic + keyword (hybrid) job search in Elasticsearch: index
  mapping with a dense_vector field, our own embeddings (NOT ELSER on the free license),
  BM25 + kNN fused with an RRF retriever, and the rule that Elasticsearch is a derived,
  rebuildable index. Load whenever working on the Search context, ES mappings, the indexing
  projection, embeddings, or query building.
---

# Elasticsearch Hybrid Search

## Core rules

- **Elasticsearch is a derived, disposable index**, built from PostgreSQL (the system of record). You must be able to drop it and rebuild fully from Postgres — provide a re-index use case. This protects you when the embedding model or mapping changes.
- **No ELSER.** On the free self-hosted Basic license, ELSER is unavailable. We generate **our own embeddings** and store them in a `dense_vector` field, fused with BM25 via an **RRF retriever**.
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
- Query: an **RRF retriever** with two sub-retrievers — a `standard` match query (lexical, BM25) and a kNN query over the vector (semantic) — merged into one ranked list. Apply shared filters (location, contract, remote) across both.
- Natural-language search: the LLM turns the user's phrase into structured filters + a semantic query, which this context executes. The LLM never talks to ES directly.

## Match explanation (RAG)

- The only place RAG belongs. Retrieve the job + candidate profile, then have the LLM explain skill-by-skill overlap and honestly flag gaps, grounded in ROME codes and the actual job text. It must not invent requirements absent from the source.
