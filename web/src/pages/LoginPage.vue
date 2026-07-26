<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageShell from '@/components/layout/PageShell.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { login, AuthError } from '@/lib/authApi'
import { setToken } from '@/lib/authStore'

const route = useRoute()
const router = useRouter()

const email = ref('')
const password = ref('')
const status = ref<'idle' | 'submitting' | 'error'>('idle')
const errorMessage = ref<string | null>(null)

async function submit() {
  status.value = 'submitting'
  errorMessage.value = null

  try {
    const token = await login(email.value, password.value)
    setToken(token)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    await router.push(redirect)
  } catch (error) {
    status.value = 'error'
    errorMessage.value = error instanceof AuthError ? error.message : 'Something unexpected went wrong.'
  }
}
</script>

<template>
  <PageShell>
    <div class="mx-auto w-full max-w-sm">
      <div class="mb-8 space-y-2 text-center">
        <h1 class="font-heading text-2xl font-medium tracking-tight text-foreground">
          Welcome back.
        </h1>
        <p class="text-sm text-muted-foreground">
          Sign in to see your CVs and matches.
        </p>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div class="space-y-1.5">
          <label for="email" class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">Email</label>
          <Input id="email" v-model="email" type="email" autocomplete="email" required class="h-10" />
        </div>
        <div class="space-y-1.5">
          <label for="password" class="font-mono text-[0.6875rem] tracking-wide text-muted-foreground/60 uppercase">Password</label>
          <Input id="password" v-model="password" type="password" autocomplete="current-password" required class="h-10" />
        </div>

        <div v-if="status === 'error'" class="rounded-xl border border-destructive/20 bg-destructive/5 px-4 py-3">
          <p class="text-sm text-destructive/90">{{ errorMessage }}</p>
        </div>

        <Button type="submit" class="w-full" :disabled="status === 'submitting'">
          {{ status === 'submitting' ? 'Signing in…' : 'Sign in' }}
        </Button>
      </form>

      <p class="mt-6 text-center text-sm text-muted-foreground">
        No account yet?
        <RouterLink :to="{ name: 'register' }" class="text-signal hover:underline">Create one</RouterLink>
      </p>
    </div>
  </PageShell>
</template>
