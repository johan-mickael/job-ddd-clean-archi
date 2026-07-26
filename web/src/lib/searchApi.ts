export interface SearchResultItem {
  jobId: string
  title: string
  companyName: string
  locationLabel: string | null
  applyUrl: string
  publishedAt: string
  score: number
}

export interface SearchResponse {
  results: SearchResultItem[]
  page: number
  pageSize: number
  total: number
  totalPages: number
}

export interface SearchParams {
  freeText: string
  contractType?: string | null
  romeCode?: string | null
  page?: number
  pageSize?: number
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
): Promise<SearchResponse> {
  const query = new URLSearchParams({ q: params.freeText })
  if (params.contractType) query.set('contractType', params.contractType)
  if (params.romeCode) query.set('romeCode', params.romeCode)
  if (params.page) query.set('page', String(params.page))
  if (params.pageSize) query.set('pageSize', String(params.pageSize))

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

  return (await response.json()) as SearchResponse
}
