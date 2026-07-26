import { apiFetch } from '@/lib/apiFetch'

export class CvUploadError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'CvUploadError'
  }
}

export async function uploadCv(file: File): Promise<string> {
  const body = new FormData()
  body.set('cv', file)

  let response: Response
  try {
    response = await apiFetch('/api/cv', { method: 'POST', body })
  } catch {
    throw new CvUploadError('The CV upload service could not be reached.')
  }

  if (!response.ok) {
    const payload = (await response.json().catch(() => null)) as { error?: string } | null
    throw new CvUploadError(payload?.error ?? `Upload failed (HTTP ${response.status}).`)
  }

  const data = (await response.json()) as { candidateProfileId: string }
  return data.candidateProfileId
}
