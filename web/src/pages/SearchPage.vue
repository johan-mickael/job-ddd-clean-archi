<script setup lang="ts">
import { useJobSearch } from '@/composables/useJobSearch'
import PageShell from '@/components/layout/PageShell.vue'
import SearchForm from '@/components/search/SearchForm.vue'
import SearchStatus from '@/components/search/SearchStatus.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'
import ResultCard from '@/components/search/ResultCard.vue'
import { Skeleton } from '@/components/ui/skeleton'

const {
  freeText,
  contractType,
  romeCode,
  page,
  status,
  results,
  total,
  totalPages,
  elapsedMs,
  errorMessage,
  searchNow,
  goToPage,
} = useJobSearch()

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
  <PageShell>
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
        :result-count="total"
        :elapsed-ms="elapsedMs"
        :error-message="errorMessage"
        :query="freeText"
      />
    </div>

    <div class="space-y-3">
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
          :rank="(page - 1) * 10 + index + 1"
          :max-score="maxScore()"
        />
        <SearchPagination :page="page" :total-pages="totalPages" @navigate="goToPage" />
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
  </PageShell>
</template>
