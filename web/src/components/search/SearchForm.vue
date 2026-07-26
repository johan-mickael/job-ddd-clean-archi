<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const freeText = defineModel<string>('freeText', { required: true })
const contractType = defineModel<string | null>('contractType', { required: true })
const romeCode = defineModel<string>('romeCode', { required: true })

defineProps<{
  onSubmit: () => void
}>()

const CONTRACT_TYPES: { value: string; label: string }[] = [
  { value: 'cdi', label: 'Permanent' },
  { value: 'cdd', label: 'Fixed-term' },
  { value: 'interim', label: 'Temporary' },
  { value: 'other', label: 'Other' },
]

function toggleContractType(value: string) {
  contractType.value = contractType.value === value ? null : value
}
</script>

<template>
  <form class="space-y-3" @submit.prevent="onSubmit">
    <div class="relative">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
      <Input
        v-model="freeText"
        type="search"
        placeholder="Remote backend roles that don&rsquo;t require a degree&hellip;"
        class="h-14 rounded-xl border-white/10 bg-card/60 pr-4 pl-12 font-heading text-lg placeholder:text-muted-foreground/50 focus-visible:border-signal/50 focus-visible:ring-signal/20"
        autofocus
      />
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <span class="mr-1 font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">Contract</span>
      <button
        v-for="option in CONTRACT_TYPES"
        :key="option.value"
        type="button"
        :class="cn(
          'rounded-full border px-3 py-1 text-xs font-medium transition-colors',
          contractType === option.value
            ? 'border-signal/50 bg-signal/15 text-foreground'
            : 'border-white/10 text-muted-foreground hover:border-white/20 hover:text-foreground',
        )"
        @click="toggleContractType(option.value)"
      >
        {{ option.label }}
      </button>

      <span class="mx-2 h-4 w-px bg-white/10" />

      <span class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">ROME code</span>
      <Input
        v-model="romeCode"
        placeholder="e.g. M1805"
        class="h-7 w-28 rounded-full border-white/10 bg-transparent px-3 font-mono text-xs placeholder:text-muted-foreground/40"
      />

      <Button type="submit" size="sm" variant="ghost" class="ml-auto h-7 text-xs text-muted-foreground hover:text-foreground">
        Search now
      </Button>
    </div>
  </form>
</template>
