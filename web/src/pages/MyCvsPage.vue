<script setup lang="ts">
import { onMounted, ref } from 'vue'
import PageShell from '@/components/layout/PageShell.vue'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { fetchMyCandidateProfiles, MyCandidateProfilesUnavailableError, type MyCandidateProfileSummary } from '@/lib/myCvApi'
import { formatDate } from '@/lib/formatDate'

type LoadStatus = 'loading' | 'success' | 'error'

const status = ref<LoadStatus>('loading')
const profiles = ref<MyCandidateProfileSummary[]>([])
const errorMessage = ref<string | null>(null)

const SENIORITY_LABELS: Record<string, string> = {
  junior: 'Junior',
  mid: 'Mid-level',
  senior: 'Senior',
  lead: 'Lead',
  principal: 'Principal',
}

onMounted(async () => {
  try {
    profiles.value = await fetchMyCandidateProfiles()
    status.value = 'success'
  } catch (error) {
    status.value = 'error'
    errorMessage.value = error instanceof MyCandidateProfilesUnavailableError ? error.message : 'Something unexpected went wrong.'
  }
})
</script>

<template>
  <PageShell>
    <div class="mb-8 flex items-end justify-between gap-4">
      <div class="space-y-2">
        <h1 class="font-heading text-2xl font-medium tracking-tight text-foreground">
          Your CVs.
        </h1>
        <p class="text-sm text-muted-foreground">
          Every profile you&rsquo;ve extracted, ready to match against jobs again.
        </p>
      </div>
      <RouterLink
        :to="{ name: 'upload-cv' }"
        class="flex shrink-0 items-center gap-1.5 rounded-full border border-white/10 px-3 py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:border-signal/40 hover:text-foreground"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M5 12h14" /><path d="M12 5v14" /></svg>
        Upload a new CV
      </RouterLink>
    </div>

    <div v-if="status === 'loading'" class="space-y-3">
      <Skeleton class="h-16 w-full rounded-xl bg-card/60" />
      <Skeleton class="h-16 w-full rounded-xl bg-card/60" />
    </div>

    <div v-else-if="status === 'error'" class="rounded-xl border border-destructive/20 bg-destructive/5 px-5 py-8 text-center">
      <p class="text-sm text-destructive/90">{{ errorMessage }} Try again in a moment.</p>
    </div>

    <div v-else-if="profiles.length === 0" class="rounded-xl border border-white/8 bg-card/40 px-5 py-10 text-center">
      <p class="text-sm text-muted-foreground">
        No CVs yet &mdash;
        <RouterLink :to="{ name: 'upload-cv' }" class="text-signal hover:underline">upload one</RouterLink>
        to find your matches.
      </p>
    </div>

    <ul v-else class="space-y-2.5">
      <li v-for="profile in profiles" :key="profile.candidateProfileId">
        <RouterLink
          :to="{ name: 'cv-profile', params: { candidateProfileId: profile.candidateProfileId } }"
          class="flex items-center justify-between gap-4 rounded-xl border border-white/8 bg-card/40 px-5 py-4 transition-colors hover:border-signal/30"
        >
          <div class="min-w-0 space-y-1">
            <p class="truncate text-sm font-medium text-foreground">{{ profile.sourceFileName }}</p>
            <p class="font-mono text-[0.6875rem] text-muted-foreground/60">Extracted {{ formatDate(profile.extractedAt) }}</p>
          </div>
          <Badge variant="secondary" class="shrink-0">{{ SENIORITY_LABELS[profile.seniority] ?? profile.seniority }}</Badge>
        </RouterLink>
      </li>
    </ul>
  </PageShell>
</template>
