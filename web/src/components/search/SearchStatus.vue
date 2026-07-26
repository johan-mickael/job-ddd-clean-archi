<script setup lang="ts">
import type { SearchStatus } from '@/composables/useJobSearch'

defineProps<{
  status: SearchStatus
  resultCount: number
  elapsedMs: number | null
  errorMessage: string | null
  query: string
}>()
</script>

<template>
  <div class="flex h-5 items-center gap-2 font-mono text-xs text-muted-foreground">
    <template v-if="status === 'searching'">
      <span class="relative flex size-2">
        <span class="absolute inline-flex size-full animate-ping rounded-full bg-signal/60" />
        <span class="relative inline-flex size-2 rounded-full bg-signal" />
      </span>
      <span>Embedding query &amp; ranking against the index&hellip;</span>
    </template>

    <template v-else-if="status === 'success'">
      <span class="size-2 rounded-full bg-signal" />
      <span>{{ resultCount }} match{{ resultCount === 1 ? '' : 'es' }} for &ldquo;{{ query }}&rdquo; &middot; {{ elapsedMs }}ms &middot; hybrid BM25 + kNN, fused with RRF</span>
    </template>

    <template v-else-if="status === 'empty'">
      <span class="size-2 rounded-full bg-muted-foreground/50" />
      <span>No matches for &ldquo;{{ query }}&rdquo; &middot; {{ elapsedMs }}ms &middot; try removing a filter or broadening the phrase</span>
    </template>

    <template v-else-if="status === 'error'">
      <span class="size-2 rounded-full bg-destructive" />
      <span class="text-destructive/90">{{ errorMessage }}</span>
    </template>
  </div>
</template>
