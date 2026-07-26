<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import RelevanceBar from './RelevanceBar.vue'
import type { SearchResultItem } from '@/lib/searchApi'
import { formatDate } from '@/lib/formatDate'

defineProps<{
  result: SearchResultItem
  rank: number
  maxScore: number
}>()
</script>

<template>
  <Card
    class="group border-white/8 bg-card/60 py-0 transition-colors hover:border-signal/40"
  >
    <CardContent class="flex items-start gap-4 p-5">
      <span
        class="mt-0.5 shrink-0 font-mono text-xs text-muted-foreground/60 tabular-nums"
      >{{ String(rank).padStart(2, '0') }}</span>

      <div class="min-w-0 flex-1 space-y-2">
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
          <RouterLink
            :to="{ name: 'job-detail', params: { jobId: result.jobId } }"
            class="font-heading text-base font-medium text-foreground hover:text-signal hover:underline"
          >
            {{ result.title }}
          </RouterLink>
          <RelevanceBar :score="result.score" :max-score="maxScore" />
        </div>

        <p class="text-sm text-muted-foreground">
          {{ result.companyName }}
          <template v-if="result.locationLabel">
            <span class="mx-1.5 text-muted-foreground/40">&middot;</span>
            {{ result.locationLabel }}
          </template>
          <span class="mx-1.5 text-muted-foreground/40">&middot;</span>
          <span class="font-mono text-xs">Published {{ formatDate(result.publishedAt) }}</span>
        </p>

        <div class="flex items-center gap-2 pt-1">
          <Button as="a" :href="result.applyUrl" target="_blank" rel="noopener noreferrer" size="sm" variant="secondary" class="gap-1.5">
            Apply on France Travail
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M7 17 17 7" /><path d="M7 7h10v10" /></svg>
          </Button>
          <Button as-child size="sm" variant="ghost">
            <RouterLink :to="{ name: 'job-detail', params: { jobId: result.jobId } }">
              View details
            </RouterLink>
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
