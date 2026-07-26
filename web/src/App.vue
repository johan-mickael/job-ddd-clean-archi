<script setup lang="ts">
import { useJobSearch } from '@/composables/useJobSearch'
import SearchForm from '@/components/search/SearchForm.vue'
import SearchStatus from '@/components/search/SearchStatus.vue'
import ResultCard from '@/components/search/ResultCard.vue'
import { Skeleton } from '@/components/ui/skeleton'

const { freeText, contractType, romeCode, status, results, elapsedMs, errorMessage, searchNow } =
  useJobSearch()

const EXAMPLE_QUERIES = [
  'remote backend roles that don’t require a degree',
  'senior PHP with Symfony experience in Paris',
  'entry-level data analyst, no experience needed',
]

function maxScore() {
  return results.value.reduce((max, item) => Math.max(max, item.score), 0)
}
</script>

<template>
  <div class="min-h-svh bg-background">
    <div
      class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgb(139_127_255_/_0.15),transparent)]"
    />

    <main class="relative mx-auto flex min-h-svh max-w-2xl flex-col px-6 py-16">
      <header class="mb-10 flex items-center gap-2.5">
        <span class="flex size-6 items-center justify-center rounded-md bg-signal/15 text-signal">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>
        </span>
        <span class="font-heading text-sm font-medium tracking-tight text-foreground">
          semantic<span class="text-muted-foreground">/jobs</span>
        </span>
      </header>

      <div class="mb-8 space-y-2">
        <h1 class="font-heading text-2xl font-medium tracking-tight text-balance text-foreground">
          Search jobs by meaning, not just keywords.
        </h1>
        <p class="text-sm text-muted-foreground">
          Hybrid retrieval over live France Travail listings &mdash; lexical and semantic search, fused.
        </p>
      </div>

      <SearchForm
        v-model:free-text="freeText"
        v-model:contract-type="contractType"
        v-model:rome-code="romeCode"
        :on-submit="searchNow"
      />

      <div class="mt-4 mb-6">
        <SearchStatus
          :status="status"
          :result-count="results.length"
          :elapsed-ms="elapsedMs"
          :error-message="errorMessage"
          :query="freeText"
        />
      </div>

      <div class="flex-1 space-y-3">
        <template v-if="status === 'idle'">
          <p class="mb-3 font-mono text-[0.6875rem] tracking-wide text-muted-foreground/50 uppercase">
            Try
          </p>
          <button
            v-for="example in EXAMPLE_QUERIES"
            :key="example"
            type="button"
            class="block w-full rounded-lg border border-white/8 bg-card/40 px-4 py-3 text-left text-sm text-muted-foreground transition-colors hover:border-signal/30 hover:text-foreground"
            @click="freeText = example"
          >
            &ldquo;{{ example }}&rdquo;
          </button>
        </template>

        <template v-else-if="status === 'searching' && results.length === 0">
          <Skeleton v-for="i in 3" :key="i" class="h-24 w-full rounded-xl bg-card/60" />
        </template>

        <template v-else-if="status === 'success' || status === 'searching'">
          <ResultCard
            v-for="(result, index) in results"
            :key="result.jobId"
            :result="result"
            :rank="index + 1"
            :max-score="maxScore()"
          />
        </template>

        <template v-else-if="status === 'empty'">
          <div class="rounded-xl border border-white/8 bg-card/40 px-5 py-8 text-center">
            <p class="text-sm text-muted-foreground">
              Nothing matched. Try dropping the contract or ROME filter, or rephrase &mdash;
              the index searches meaning, not exact wording.
            </p>
          </div>
        </template>

        <template v-else-if="status === 'error'">
          <div class="rounded-xl border border-destructive/20 bg-destructive/5 px-5 py-8 text-center">
            <p class="text-sm text-destructive/90">
              {{ errorMessage }} Try again in a moment.
            </p>
          </div>
        </template>
      </div>

      <footer class="mt-16 flex items-center justify-between font-mono text-[0.6875rem] text-muted-foreground/40">
        <span>Listings via France Travail &middot; always apply on their site</span>
      </footer>
    </main>
  </div>
</template>
