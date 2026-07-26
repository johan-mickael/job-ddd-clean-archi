<script setup lang="ts">
import { Button } from '@/components/ui/button'

const props = defineProps<{
  page: number
  totalPages: number
}>()

const emit = defineEmits<{
  navigate: [page: number]
}>()
</script>

<template>
  <div v-if="totalPages > 1" class="flex items-center justify-between pt-2">
    <Button
      variant="outline"
      size="sm"
      :disabled="page <= 1"
      @click="emit('navigate', page - 1)"
    >
      Previous
    </Button>

    <span class="font-mono text-xs text-muted-foreground">
      Page {{ props.page }} of {{ props.totalPages }}
    </span>

    <Button
      variant="outline"
      size="sm"
      :disabled="page >= totalPages"
      @click="emit('navigate', page + 1)"
    >
      Next
    </Button>
  </div>
</template>
