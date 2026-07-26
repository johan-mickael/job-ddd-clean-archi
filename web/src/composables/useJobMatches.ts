import { ref, watch } from 'vue'
import { fetchMatches, CandidateProfileNotFoundError, MatchesUnavailableError } from '@/lib/matchesApi'
import type { SearchResultItem } from '@/lib/searchApi'

export type MatchesStatus = 'loading' | 'success' | 'empty' | 'not-found' | 'error'

const PAGE_SIZE = 10

export function useJobMatches(candidateProfileId: () => string) {
  const page = ref(1)
  const status = ref<MatchesStatus>('loading')
  const results = ref<SearchResultItem[]>([])
  const total = ref(0)
  const totalPages = ref(0)
  const errorMessage = ref<string | null>(null)

  async function load() {
    status.value = 'loading'

    try {
      const found = await fetchMatches(candidateProfileId(), page.value, PAGE_SIZE)
      results.value = found.results
      total.value = found.total
      totalPages.value = found.totalPages
      status.value = found.results.length > 0 ? 'success' : 'empty'
    } catch (error) {
      results.value = []
      total.value = 0
      totalPages.value = 0

      if (error instanceof CandidateProfileNotFoundError) {
        status.value = 'not-found'
        return
      }

      status.value = 'error'
      errorMessage.value = error instanceof MatchesUnavailableError ? error.message : 'Something unexpected went wrong.'
    }
  }

  function goToPage(target: number) {
    if (target < 1 || target > totalPages.value || target === page.value) return
    page.value = target
    void load()
  }

  watch(candidateProfileId, () => {
    page.value = 1
    void load()
  }, { immediate: true })

  return { page, status, results, total, totalPages, errorMessage, goToPage }
}
