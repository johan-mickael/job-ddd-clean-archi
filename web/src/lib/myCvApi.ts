import { apiFetch } from '@/lib/apiFetch'

export interface MyCandidateProfileSummary {
  candidateProfileId: string
  sourceFileName: string
  extractedAt: string
  seniority: string
}

export class MyCandidateProfilesUnavailableError extends Error {
  constructor(cause?: unknown) {
    super('Your CVs could not be loaded.')
    this.name = 'MyCandidateProfilesUnavailableError'
    this.cause = cause
  }
}

export async function fetchMyCandidateProfiles(): Promise<MyCandidateProfileSummary[]> {
  let response: Response
  try {
    response = await apiFetch('/api/cv')
  } catch (error) {
    throw new MyCandidateProfilesUnavailableError(error)
  }

  if (!response.ok) {
    throw new MyCandidateProfilesUnavailableError(`HTTP ${response.status}`)
  }

  const data = (await response.json()) as { profiles: MyCandidateProfileSummary[] }
  return data.profiles
}
