import { apiFetch } from '@/lib/apiFetch'

export interface CandidateProfileLanguage {
  name: string
  proficiency: string
}

export interface CandidateProfileSkill {
  label: string
  yearsOfExperience: number | null
}

export interface CandidateProfile {
  candidateProfileId: string
  sourceFileName: string
  extractedAt: string
  seniority: string
  domains: string[]
  roles: string[]
  languages: CandidateProfileLanguage[]
  skills: CandidateProfileSkill[]
}

export class CandidateProfileUnavailableError extends Error {
  constructor(cause?: unknown) {
    super('This profile could not be loaded.')
    this.name = 'CandidateProfileUnavailableError'
    this.cause = cause
  }
}

export class CandidateProfileNotFoundError extends Error {
  constructor() {
    super('This profile no longer exists.')
    this.name = 'CandidateProfileNotFoundError'
  }
}

export async function fetchCandidateProfile(candidateProfileId: string): Promise<CandidateProfile> {
  let response: Response
  try {
    response = await apiFetch(`/api/cv/${encodeURIComponent(candidateProfileId)}`)
  } catch (error) {
    throw new CandidateProfileUnavailableError(error)
  }

  if (response.status === 404) {
    throw new CandidateProfileNotFoundError()
  }

  if (!response.ok) {
    throw new CandidateProfileUnavailableError(`HTTP ${response.status}`)
  }

  return (await response.json()) as CandidateProfile
}
