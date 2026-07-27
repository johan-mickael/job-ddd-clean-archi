import { apiFetch } from '@/lib/apiFetch'
import type { SearchResponse } from './searchApi'

export class MatchesUnavailableError extends Error {
  constructor(cause?: unknown) {
    super('Matches could not be loaded.')
    this.name = 'MatchesUnavailableError'
    this.cause = cause
  }
}

export class CandidateProfileNotFoundError extends Error {
  constructor() {
    super('This candidate profile no longer exists.')
    this.name = 'CandidateProfileNotFoundError'
  }
}

export async function fetchMatches(
  candidateProfileId: string,
  page = 1,
  pageSize = 10,
): Promise<SearchResponse> {
  const query = new URLSearchParams({ page: String(page), pageSize: String(pageSize) })

  let response: Response
  try {
    response = await apiFetch(`/api/cv/${encodeURIComponent(candidateProfileId)}/matches?${query.toString()}`)
  } catch (error) {
    throw new MatchesUnavailableError(error)
  }

  if (response.status === 404) {
    throw new CandidateProfileNotFoundError()
  }

  if (!response.ok) {
    throw new MatchesUnavailableError(`HTTP ${response.status}`)
  }

  return (await response.json()) as SearchResponse
}
