<script setup lang="ts">
import { ref, watch } from 'vue'
import PageShell from '@/components/layout/PageShell.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import {
  fetchCandidateProfile,
  CandidateProfileNotFoundError,
  CandidateProfileUnavailableError,
  type CandidateProfile,
} from '@/lib/candidateProfileApi'
import { formatDate } from '@/lib/formatDate'

const props = defineProps<{ candidateProfileId: string }>()

type LoadStatus = 'loading' | 'success' | 'not-found' | 'error'

const status = ref<LoadStatus>('loading')
const profile = ref<CandidateProfile | null>(null)
const errorMessage = ref<string | null>(null)

async function load(candidateProfileId: string) {
  status.value = 'loading'
  profile.value = null

  try {
    profile.value = await fetchCandidateProfile(candidateProfileId)
    status.value = 'success'
  } catch (error) {
    if (error instanceof CandidateProfileNotFoundError) {
      status.value = 'not-found'
      return
    }
    status.value = 'error'
    errorMessage.value = error instanceof CandidateProfileUnavailableError ? error.message : 'Something unexpected went wrong.'
  }
}

watch(() => props.candidateProfileId, (id) => void load(id), { immediate: true })

const SENIORITY_LABELS: Record<string, string> = {
  junior: 'Junior',
  mid: 'Mid-level',
  senior: 'Senior',
  lead: 'Lead',
  principal: 'Principal',
}

function printProfile() {
  window.print()
}

const PROFICIENCY_LABELS: Record<string, string> = {
  basic: 'Basic',
  conversational: 'Conversational',
  fluent: 'Fluent',
  native: 'Native',
}
</script>

<template>
  <PageShell>
    <RouterLink
      :to="{ name: 'upload-cv' }"
      class="mb-6 inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground print:hidden"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="m15 18-6-6 6-6" /></svg>
      Upload a different CV
    </RouterLink>

    <div v-if="status === 'loading'" class="space-y-4">
      <Skeleton class="h-8 w-2/3 rounded-lg bg-card/60" />
      <Skeleton class="h-4 w-1/3 rounded-lg bg-card/60" />
      <Skeleton class="h-48 w-full rounded-xl bg-card/60" />
    </div>

    <div v-else-if="status === 'not-found'" class="rounded-xl border border-white/8 bg-card/40 px-5 py-8 text-center">
      <p class="text-sm text-muted-foreground">
        This profile no longer exists &mdash;
        <RouterLink :to="{ name: 'upload-cv' }" class="text-signal hover:underline">upload your CV again</RouterLink>.
      </p>
    </div>

    <div v-else-if="status === 'error'" class="rounded-xl border border-destructive/20 bg-destructive/5 px-5 py-8 text-center">
      <p class="text-sm text-destructive/90">{{ errorMessage }} Try again in a moment.</p>
    </div>

    <article v-else-if="profile" class="space-y-6">
      <header class="space-y-1.5">
        <p class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/50 uppercase">Your profile</p>
        <h1 class="font-heading text-2xl font-medium text-balance text-foreground">
          {{ profile.roles[0] ?? 'Extracted profile' }}
        </h1>
        <p class="font-mono text-xs text-muted-foreground/60">
          From {{ profile.sourceFileName }} &middot; extracted {{ formatDate(profile.extractedAt) }}
        </p>
      </header>

      <div class="flex flex-wrap gap-1.5 print:hidden">
        <Button as-child size="sm" class="gap-1.5">
          <RouterLink :to="{ name: 'cv-matches', params: { candidateProfileId: profile.candidateProfileId } }">
            Find matching jobs
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
          </RouterLink>
        </Button>
        <Button size="sm" variant="secondary" class="gap-1.5" @click="printProfile">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M6 9V2h12v7" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><rect x="6" y="14" width="12" height="8" /></svg>
          Print / save as PDF
        </Button>
      </div>

      <p class="font-mono text-[0.6875rem] text-muted-foreground/50 print:hidden">
        This URL is your profile &mdash; bookmark it to come back, or print/save it as a PDF above.
      </p>

      <div class="grid grid-cols-2 gap-4 border-y border-white/8 py-4 text-sm">
        <div>
          <p class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">Seniority</p>
          <p class="text-foreground">{{ SENIORITY_LABELS[profile.seniority] ?? profile.seniority }}</p>
        </div>
        <div v-if="profile.domains.length > 0">
          <p class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">Domains</p>
          <p class="text-foreground">{{ profile.domains.join(', ') }}</p>
        </div>
      </div>

      <section v-if="profile.roles.length > 0" class="space-y-2">
        <h2 class="font-heading text-sm font-medium text-foreground">Roles</h2>
        <div class="flex flex-wrap gap-1.5">
          <Badge v-for="role in profile.roles" :key="role" variant="secondary">{{ role }}</Badge>
        </div>
      </section>

      <section v-if="profile.skills.length > 0" class="space-y-2">
        <h2 class="font-heading text-sm font-medium text-foreground">Skills</h2>
        <div class="flex flex-wrap gap-1.5">
          <Badge v-for="skill in profile.skills" :key="skill.label" variant="outline" class="font-mono">
            {{ skill.label }}<template v-if="skill.yearsOfExperience"> &middot; {{ skill.yearsOfExperience }}y</template>
          </Badge>
        </div>
      </section>

      <section v-if="profile.languages.length > 0" class="space-y-2">
        <h2 class="font-heading text-sm font-medium text-foreground">Languages</h2>
        <div class="flex flex-wrap gap-1.5">
          <Badge v-for="language in profile.languages" :key="language.name" variant="outline">
            {{ language.name }} &middot; {{ PROFICIENCY_LABELS[language.proficiency] ?? language.proficiency }}
          </Badge>
        </div>
      </section>
    </article>
  </PageShell>
</template>
