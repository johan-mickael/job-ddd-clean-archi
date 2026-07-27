import { clearToken, getToken } from '@/lib/authStore'

export class UnauthenticatedError extends Error {
  constructor() {
    super('Your session has expired.')
    this.name = 'UnauthenticatedError'
  }
}

export async function apiFetch(input: string, init: RequestInit = {}): Promise<Response> {
  const token = getToken()
  const headers = new Headers(init.headers)
  if (token) headers.set('Authorization', `Bearer ${token}`)

  const response = await fetch(input, { ...init, headers })

  if (response.status === 401) {
    clearToken()
    if (!window.location.pathname.startsWith('/login')) {
      window.location.assign(`/login?redirect=${encodeURIComponent(window.location.pathname)}`)
    }
    throw new UnauthenticatedError()
  }

  return response
}
