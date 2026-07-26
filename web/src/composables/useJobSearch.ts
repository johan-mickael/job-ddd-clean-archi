import { ref, watch } from 'vue'
import { searchJobs, SearchUnavailableError, type SearchResultItem } from '@/lib/searchApi'

export type SearchStatus = 'idle' | 'searching' | 'success' | 'empty' | 'error'

const DEBOUNCE_MS = 500
const PAGE_SIZE = 10

export function useJobSearch() {
  const freeText = ref('')
  const contractType = ref<string | null>(null)
  const romeCode = ref('')
  const page = ref(1)

  const status = ref<SearchStatus>('idle')
  const results = ref<SearchResultItem[]>([])
  const total = ref(0)
  const totalPages = ref(0)
  const elapsedMs = ref<number | null>(null)
  const errorMessage = ref<string | null>(null)

  let debounceHandle: ReturnType<typeof setTimeout> | undefined
  let inFlight: AbortController | undefined

  async function runSearch() {
    const text = freeText.value.trim()
    if (!text) {
      status.value = 'idle'
      results.value = []
      total.value = 0
      totalPages.value = 0
      elapsedMs.value = null
      return
    }

    inFlight?.abort()
    const controller = new AbortController()
    inFlight = controller

    status.value = 'searching'
    errorMessage.value = null
    const startedAt = performance.now()

    try {
      const found = await searchJobs(
        {
          freeText: text,
          contractType: contractType.value,
          romeCode: romeCode.value.trim() || null,
          page: page.value,
          pageSize: PAGE_SIZE,
        },
        controller.signal,
      )
      if (controller.signal.aborted) return

      elapsedMs.value = Math.round(performance.now() - startedAt)
      results.value = found.results
      total.value = found.total
      totalPages.value = found.totalPages
      status.value = found.results.length > 0 ? 'success' : 'empty'
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') return
      results.value = []
      total.value = 0
      totalPages.value = 0
      elapsedMs.value = null
      status.value = 'error'
      errorMessage.value =
        error instanceof SearchUnavailableError
          ? error.message
          : 'Something unexpected went wrong.'
    }
  }

  function searchNow() {
    clearTimeout(debounceHandle)
    void runSearch()
  }

  function searchDebounced() {
    clearTimeout(debounceHandle)
    debounceHandle = setTimeout(() => void runSearch(), DEBOUNCE_MS)
  }

  function goToPage(target: number) {
    if (target < 1 || target > totalPages.value || target === page.value) return
    page.value = target
    searchNow()
  }

  // A new query or filter always starts back at page 1 - jumping to a page
  // number that made sense for the old result set would be confusing.
  watch([freeText, contractType, romeCode], () => {
    page.value = 1
  })

  watch(freeText, searchDebounced)
  watch([contractType, romeCode], searchNow)

  return {
    freeText,
    contractType,
    romeCode,
    page,
    status,
    results,
    total,
    totalPages,
    elapsedMs,
    errorMessage,
    searchNow,
    goToPage,
  }
}
