<script setup lang="ts">
import PageShell from '@/components/layout/PageShell.vue'
import ResultCard from '@/components/search/ResultCard.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'
import { Skeleton } from '@/components/ui/skeleton'
import { useJobMatches } from '@/composables/useJobMatches'

const props = defineProps<{ candidateProfileId: string }>()

const { page, status, results, total, totalPages, errorMessage, goToPage } = useJobMatches(
  () => props.candidateProfileId,
)

function maxScore() {
  return results.value.reduce((max, item) => Math.max(max, item.score), 0)
}
</script>

<template>
  <PageShell>
    <RouterLink
      :to="{ name: 'upload-cv' }"
      class="mb-6 inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="m15 18-6-6 6-6" /></svg>
      Upload a different CV
    </RouterLink>

    <div class="mb-6 space-y-2">
      <h1 class="font-heading text-2xl font-medium tracking-tight text-balance text-foreground">
        Jobs matched to your profile.
      </h1>
      <p v-if="status === 'success' || status === 'empty'" class="font-mono text-xs text-muted-foreground">
        {{ total }} match{{ total === 1 ? '' : 'es' }} &middot; ranked by your extracted roles and skills
      </p>
    </div>

    <div class="space-y-3">
      <template v-if="status === 'loading'">
        <Skeleton v-for="i in 3" :key="i" class="h-24 w-full rounded-xl bg-card/60" />
      </template>

      <template v-else-if="status === 'success'">
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
            No matches found for this profile yet.
          </p>
        </div>
      </template>

      <template v-else-if="status === 'not-found'">
        <div class="rounded-xl border border-white/8 bg-card/40 px-5 py-8 text-center">
          <p class="text-sm text-muted-foreground">
            This profile no longer exists &mdash;
            <RouterLink :to="{ name: 'upload-cv' }" class="text-signal hover:underline">upload your CV again</RouterLink>.
          </p>
        </div>
      </template>

      <template v-else-if="status === 'error'">
        <div class="rounded-xl border border-destructive/20 bg-destructive/5 px-5 py-8 text-center">
          <p class="text-sm text-destructive/90">{{ errorMessage }} Try again in a moment.</p>
        </div>
      </template>
    </div>
  </PageShell>
</template>
