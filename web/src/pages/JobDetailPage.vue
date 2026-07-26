<script setup lang="ts">
import { ref, watch } from 'vue'
import PageShell from '@/components/layout/PageShell.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import {
  fetchJob,
  JobNotFoundError,
  JobUnavailableError,
  type JobDetail,
} from '@/lib/jobApi'
import { formatDate } from '@/lib/formatDate'

const props = defineProps<{ jobId: string }>()

type LoadStatus = 'loading' | 'success' | 'not-found' | 'error'

const status = ref<LoadStatus>('loading')
const job = ref<JobDetail | null>(null)
const errorMessage = ref<string | null>(null)

async function load(jobId: string) {
  status.value = 'loading'
  job.value = null

  try {
    job.value = await fetchJob(jobId)
    status.value = 'success'
  } catch (error) {
    if (error instanceof JobNotFoundError) {
      status.value = 'not-found'
      return
    }
    status.value = 'error'
    errorMessage.value = error instanceof JobUnavailableError ? error.message : 'Something unexpected went wrong.'
  }
}

watch(() => props.jobId, (jobId) => void load(jobId), { immediate: true })

const CONTRACT_TYPE_LABELS: Record<string, string> = {
  cdi: 'Permanent (CDI)',
  cdd: 'Fixed-term (CDD)',
  interim: 'Temporary (Intérim)',
  other: 'Other',
}
</script>

<template>
  <PageShell wide>
    <RouterLink
      :to="{ name: 'search' }"
      class="mb-6 inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="m15 18-6-6 6-6" /></svg>
      Back to search
    </RouterLink>

    <div v-if="status === 'loading'" class="space-y-4">
      <Skeleton class="h-8 w-2/3 rounded-lg bg-card/60" />
      <Skeleton class="h-4 w-1/3 rounded-lg bg-card/60" />
      <Skeleton class="h-48 w-full rounded-xl bg-card/60" />
    </div>

    <div v-else-if="status === 'not-found'" class="rounded-xl border border-white/8 bg-card/40 px-5 py-8 text-center">
      <p class="text-sm text-muted-foreground">
        This job no longer exists &mdash; it may have expired or been removed upstream.
      </p>
    </div>

    <div v-else-if="status === 'error'" class="rounded-xl border border-destructive/20 bg-destructive/5 px-5 py-8 text-center">
      <p class="text-sm text-destructive/90">{{ errorMessage }} Try again in a moment.</p>
    </div>

    <article v-else-if="job" class="space-y-6">
      <header class="flex items-start gap-4">
        <img
          v-if="job.companyLogoUrl"
          :src="job.companyLogoUrl"
          :alt="`${job.companyName} logo`"
          class="size-14 shrink-0 rounded-lg border border-white/8 bg-white object-contain p-1.5"
        >
        <div class="min-w-0 flex-1 space-y-1.5">
          <h1 class="font-heading text-2xl font-medium text-balance text-foreground">
            {{ job.title }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ job.companyName }}
            <template v-if="job.locationLabel">
              <span class="mx-1.5 text-muted-foreground/40">&middot;</span>
              {{ job.locationLabel }}
            </template>
          </p>
          <p class="font-mono text-xs text-muted-foreground/60">
            Published {{ formatDate(job.publishedAt) }}
            <template v-if="job.updatedAt && job.updatedAt !== job.publishedAt">
              &middot; updated {{ formatDate(job.updatedAt) }}
            </template>
          </p>
        </div>
      </header>

      <div class="flex flex-wrap gap-1.5">
        <Badge variant="secondary">{{ CONTRACT_TYPE_LABELS[job.contractType] ?? job.contractType }}</Badge>
        <Badge v-if="job.romeLabel" variant="outline" class="font-mono">{{ job.romeCode }} &middot; {{ job.romeLabel }}</Badge>
        <Badge v-if="job.isWorkStudy" variant="outline">Work-study</Badge>
        <Badge v-if="job.numberOfPositions > 1" variant="outline">{{ job.numberOfPositions }} positions</Badge>
      </div>

      <Button as="a" :href="job.applyUrl" target="_blank" rel="noopener noreferrer" class="gap-1.5">
        Apply on France Travail
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M7 17 17 7" /><path d="M7 7h10v10" /></svg>
      </Button>

      <p class="text-xs text-muted-foreground/60">
        Our copy can lag &mdash; the button above always goes to the live France Travail posting.
      </p>

      <div v-if="job.salaryDescription || job.experienceRequirement" class="grid grid-cols-2 gap-4 border-y border-white/8 py-4 text-sm">
        <div v-if="job.salaryDescription">
          <p class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">Salary</p>
          <p class="text-foreground">{{ job.salaryDescription }}</p>
        </div>
        <div v-if="job.experienceRequirement">
          <p class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">Experience</p>
          <p class="text-foreground">{{ job.experienceRequirement }}</p>
        </div>
      </div>

      <section class="space-y-2">
        <h2 class="font-heading text-sm font-medium text-foreground">Description</h2>
        <p class="text-sm whitespace-pre-line text-muted-foreground">{{ job.description }}</p>
      </section>

      <section v-if="job.skills.length > 0" class="space-y-2">
        <h2 class="font-heading text-sm font-medium text-foreground">Skills</h2>
        <div class="flex flex-wrap gap-1.5">
          <Badge v-for="skill in job.skills" :key="skill.label" :variant="skill.isRequired ? 'default' : 'outline'">
            {{ skill.label }}
          </Badge>
        </div>
      </section>

      <section v-if="job.companyDescription" class="space-y-2">
        <h2 class="font-heading text-sm font-medium text-foreground">About {{ job.companyName }}</h2>
        <p class="text-sm whitespace-pre-line text-muted-foreground">{{ job.companyDescription }}</p>
      </section>
    </article>
  </PageShell>
</template>
