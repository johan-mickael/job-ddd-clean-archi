<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PageShell from '@/components/layout/PageShell.vue'
import { Button } from '@/components/ui/button'
import { uploadCv, CvUploadError } from '@/lib/cvApi'

type UploadStatus = 'idle' | 'uploading' | 'error'

const router = useRouter()
const status = ref<UploadStatus>('idle')
const errorMessage = ref<string | null>(null)
const selectedFile = ref<File | null>(null)
const isDraggingOver = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

const ACCEPTED_EXTENSIONS = ['.pdf', '.doc', '.docx']

function isAcceptedFile(file: File): boolean {
  return ACCEPTED_EXTENSIONS.some((ext) => file.name.toLowerCase().endsWith(ext))
}

function pickFile(file: File | undefined) {
  if (!file) return
  if (!isAcceptedFile(file)) {
    status.value = 'error'
    errorMessage.value = 'Only PDF, DOC, and DOCX files are supported.'
    return
  }
  status.value = 'idle'
  errorMessage.value = null
  selectedFile.value = file
}

function onDrop(event: DragEvent) {
  isDraggingOver.value = false
  pickFile(event.dataTransfer?.files?.[0])
}

function onFileInputChange(event: Event) {
  pickFile((event.target as HTMLInputElement).files?.[0])
}

async function submit() {
  if (!selectedFile.value) return

  status.value = 'uploading'
  errorMessage.value = null

  try {
    const candidateProfileId = await uploadCv(selectedFile.value)
    await router.push({ name: 'cv-profile', params: { candidateProfileId } })
  } catch (error) {
    status.value = 'error'
    errorMessage.value = error instanceof CvUploadError ? error.message : 'Something unexpected went wrong.'
  }
}
</script>

<template>
  <PageShell>
    <div class="mb-8 space-y-2">
      <h1 class="font-heading text-2xl font-medium tracking-tight text-balance text-foreground">
        Match your CV against the index.
      </h1>
      <p class="text-sm text-muted-foreground">
        We read your CV, extract your roles and skills, and rank jobs by how well they fit &mdash;
        the same hybrid search the rest of this app uses.
      </p>
    </div>

    <div
      class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed px-6 py-10 text-center transition-colors"
      :class="isDraggingOver ? 'border-signal/60 bg-signal/5' : 'border-white/10 bg-card/40'"
      @dragover.prevent="isDraggingOver = true"
      @dragleave.prevent="isDraggingOver = false"
      @drop.prevent="onDrop"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-8 text-muted-foreground/50"><path d="M14 2v6h6" /><path d="M4 22V4a2 2 0 0 1 2-2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" /></svg>

      <template v-if="selectedFile">
        <p class="font-mono text-sm text-foreground">{{ selectedFile.name }}</p>
        <Button size="sm" variant="secondary" :disabled="status === 'uploading'" @click="submit">
          {{ status === 'uploading' ? 'Extracting your profile…' : 'Find matching jobs' }}
        </Button>
        <button
          type="button"
          class="text-xs text-muted-foreground hover:text-foreground"
          @click="selectedFile = null"
        >
          choose a different file
        </button>
      </template>

      <template v-else>
        <p class="text-sm text-muted-foreground">
          Drag a PDF, DOC, or DOCX here, or
          <button type="button" class="text-signal hover:underline" @click="fileInput?.click()">browse</button>
        </p>
        <input
          ref="fileInput"
          type="file"
          accept=".pdf,.doc,.docx"
          class="hidden"
          @change="onFileInputChange"
        >
      </template>
    </div>

    <div v-if="status === 'error'" class="mt-4 rounded-xl border border-destructive/20 bg-destructive/5 px-5 py-4 text-center">
      <p class="text-sm text-destructive/90">{{ errorMessage }}</p>
    </div>

    <p class="mt-6 text-xs text-muted-foreground/60">
      Your CV is read once to extract a profile (roles, skills, seniority, languages) &mdash;
      we don&rsquo;t store the file itself, only what was extracted from it.
    </p>
  </PageShell>
</template>
