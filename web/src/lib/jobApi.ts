export interface JobSkill {
  label: string
  romeCompetencyCode: string | null
  isRequired: boolean
}

export interface JobDetail {
  jobId: string
  title: string
  description: string
  companyName: string
  companyLogoUrl: string | null
  companyDescription: string | null
  contractType: string
  applyUrl: string
  publishedAt: string
  updatedAt: string | null
  status: string
  locationLabel: string | null
  locationInseeCode: string | null
  locationPostalCode: string | null
  romeCode: string | null
  romeLabel: string | null
  experienceRequirement: string | null
  isWorkStudy: boolean
  salaryDescription: string | null
  numberOfPositions: number
  skills: JobSkill[]
}

export class JobUnavailableError extends Error {
  constructor(cause?: unknown) {
    super('This job could not be loaded.')
    this.name = 'JobUnavailableError'
    this.cause = cause
  }
}

export class JobNotFoundError extends Error {
  constructor() {
    super('This job no longer exists.')
    this.name = 'JobNotFoundError'
  }
}

export async function fetchJob(jobId: string, signal?: AbortSignal): Promise<JobDetail> {
  let response: Response
  try {
    response = await fetch(`/api/jobs/${encodeURIComponent(jobId)}`, { signal })
  } catch (error) {
    if (error instanceof DOMException && error.name === 'AbortError') throw error
    throw new JobUnavailableError(error)
  }

  if (response.status === 404) {
    throw new JobNotFoundError()
  }

  if (!response.ok) {
    throw new JobUnavailableError(`HTTP ${response.status}`)
  }

  return (await response.json()) as JobDetail
}
