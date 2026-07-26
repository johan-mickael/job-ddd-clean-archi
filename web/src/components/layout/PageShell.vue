<script setup lang="ts">
withDefaults(defineProps<{ wide?: boolean }>(), { wide: false })
</script>

<template>
  <div class="min-h-svh bg-background">
    <div
      class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgb(139_127_255_/_0.15),transparent)]"
    />

    <main
      class="relative mx-auto flex min-h-svh flex-col px-6 py-16"
      :class="wide ? 'max-w-3xl' : 'max-w-2xl'"
    >
      <RouterLink
        :to="{ name: 'search' }"
        class="mb-10 flex w-fit items-center gap-2.5"
      >
        <span class="flex size-6 items-center justify-center rounded-md bg-signal/15 text-signal">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>
        </span>
        <span class="font-heading text-sm font-medium tracking-tight text-foreground">
          semantic<span class="text-muted-foreground">/jobs</span>
        </span>
      </RouterLink>

      <div class="flex-1">
        <slot />
      </div>

      <footer class="mt-16 flex items-center justify-between font-mono text-[0.6875rem] text-muted-foreground/40">
        <span>Listings via France Travail &middot; always apply on their site</span>
      </footer>
    </main>
  </div>
</template>
