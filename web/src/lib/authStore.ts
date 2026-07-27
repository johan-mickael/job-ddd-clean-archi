import { computed, ref } from 'vue'

const TOKEN_STORAGE_KEY = 'auth_token'

interface JwtPayload {
  username?: string
  exp?: number
}

function decodePayload(token: string): JwtPayload | null {
  const parts = token.split('.')
  if (parts.length !== 3) return null

  try {
    const json = atob(parts[1].replace(/-/g, '+').replace(/_/g, '/'))
    return JSON.parse(json) as JwtPayload
  } catch {
    return null
  }
}

function isExpired(payload: JwtPayload | null): boolean {
  if (!payload?.exp) return true
  return payload.exp * 1000 <= Date.now()
}

function readStoredToken(): string | null {
  const token = localStorage.getItem(TOKEN_STORAGE_KEY)
  if (!token || isExpired(decodePayload(token))) {
    localStorage.removeItem(TOKEN_STORAGE_KEY)
    return null
  }
  return token
}

const token = ref<string | null>(readStoredToken())

export const isAuthenticated = computed(() => token.value !== null)
export const currentUserEmail = computed(() => (token.value ? (decodePayload(token.value)?.username ?? null) : null))

export function getToken(): string | null {
  return token.value
}

export function setToken(newToken: string): void {
  token.value = newToken
  localStorage.setItem(TOKEN_STORAGE_KEY, newToken)
}

export function clearToken(): void {
  token.value = null
  localStorage.removeItem(TOKEN_STORAGE_KEY)
}
