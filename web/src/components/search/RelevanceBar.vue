<script setup lang="ts">
const props = defineProps<{
  score: number
  maxScore: number
}>()

const ratio = () => (props.maxScore > 0 ? Math.min(1, props.score / props.maxScore) : 0)
</script>

<template>
  <div
    class="flex items-center gap-2"
    role="img"
    :aria-label="`Relevance: ${Math.round(ratio() * 100)} percent of the top match`"
  >
    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-white/10">
      <div
        class="h-full rounded-full bg-gradient-to-r from-signal/60 to-signal transition-[width] duration-500 ease-out"
        :style="{ width: `${ratio() * 100}%` }"
      />
    </div>
    <span class="font-mono text-xs text-muted-foreground tabular-nums">
      {{ score.toFixed(2) }}
    </span>
  </div>
</template>
