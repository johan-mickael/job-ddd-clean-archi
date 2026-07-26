import { ref, watch } from 'vue'
import { searchJobs, SearchUnavailableError, type SearchResultItem } from '@/lib/searchApi'

export type SearchStatus = 'idle' | 'searching' | 'success' | 'empty' | 'error'

const DEBOUNCE_MS = 500

export function useJobSearch() {
  const freeText = ref('')
  const contractType = ref<string | null>(null)
  const romeCode = ref('')

  const status = ref<SearchStatus>('idle')
  const results = ref<SearchResultItem[]>([])
  const elapsedMs = ref<number | null>(null)
  const errorMessage = ref<string | null>(null)

  let debounceHandle: ReturnType<typeof setTimeout> | undefined
  let inFlight: AbortController | undefined

  async function runSearch() {
    const text = freeText.value.trim()
    if (!text) {
      status.value = 'idle'
      results.value = []
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
        { freeText: text, contractType: contractType.value, romeCode: romeCode.value.trim() || null },
        controller.signal,
      )
      if (controller.signal.aborted) return

      elapsedMs.value = Math.round(performance.now() - startedAt)
      results.value = found
      status.value = found.length > 0 ? 'success' : 'empty'
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') return
      results.value = []
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

  watch(freeText, searchDebounced)
  watch([contractType, romeCode], searchNow)

  return {
    freeText,
    contractType,
    romeCode,
    status,
    results,
    elapsedMs,
    errorMessage,
    searchNow,
  }
}
