export class AuthError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'AuthError'
  }
}

async function readErrorMessage(response: Response, fallback: string): Promise<string> {
  const payload = (await response.json().catch(() => null)) as { error?: string; message?: string } | null
  return payload?.error ?? payload?.message ?? fallback
}

export async function register(email: string, password: string): Promise<void> {
  let response: Response
  try {
    response = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })
  } catch {
    throw new AuthError('The registration service could not be reached.')
  }

  if (!response.ok) {
    throw new AuthError(await readErrorMessage(response, `Registration failed (HTTP ${response.status}).`))
  }
}

export async function login(email: string, password: string): Promise<string> {
  let response: Response
  try {
    response = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })
  } catch {
    throw new AuthError('The login service could not be reached.')
  }

  if (!response.ok) {
    throw new AuthError(response.status === 401 ? 'Incorrect email or password.' : `Login failed (HTTP ${response.status}).`)
  }

  const data = (await response.json()) as { token: string }
  return data.token
}
