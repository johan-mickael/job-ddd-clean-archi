export interface SearchResultItem {
  jobId: string
  title: string
  companyName: string
  locationLabel: string | null
  applyUrl: string
  score: number
}

interface SearchResponse {
  results: SearchResultItem[]
}

export interface SearchParams {
  freeText: string
  contractType?: string | null
  romeCode?: string | null
}

export class SearchUnavailableError extends Error {
  constructor(cause?: unknown) {
    super('The search index could not be reached.')
    this.name = 'SearchUnavailableError'
    this.cause = cause
  }
}

export async function searchJobs(
  params: SearchParams,
  signal?: AbortSignal,
): Promise<SearchResultItem[]> {
  const query = new URLSearchParams({ q: params.freeText })
  if (params.contractType) query.set('contractType', params.contractType)
  if (params.romeCode) query.set('romeCode', params.romeCode)

  let response: Response
  try {
    response = await fetch(`/api/search?${query.toString()}`, { signal })
  } catch (error) {
    if (error instanceof DOMException && error.name === 'AbortError') throw error
    throw new SearchUnavailableError(error)
  }

  if (!response.ok) {
    throw new SearchUnavailableError(`HTTP ${response.status}`)
  }

  const body = (await response.json()) as SearchResponse
  return body.results
}
