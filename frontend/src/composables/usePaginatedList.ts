import { reactive, ref, shallowRef, watch } from 'vue'

import type { ListParams, Paginated } from '@/types/api'
import { getErrorMessage } from '@/utils/errors'

/**
 * List page state: filters, page number, loading and error handling.
 * Changing a filter goes back to page 1; call `reload()` after a delete.
 */
export function usePaginatedList<T, F extends ListParams>(
  fetchPage: (params: ListParams) => Promise<Paginated<T>>,
  initialFilters: F,
  pageSize = 50,
) {
  const filters = reactive({ ...initialFilters }) as F
  const page = ref(1)
  const items = shallowRef<T[]>([])
  const count = ref(0)
  const loading = ref(false)
  const error = ref<string | null>(null)
  let requestId = 0

  async function reload(): Promise<void> {
    const current = ++requestId
    loading.value = true
    error.value = null
    try {
      const data = await fetchPage({
        ...filters,
        limit: pageSize,
        offset: (page.value - 1) * pageSize,
      })
      if (current !== requestId) return
      items.value = data.results
      count.value = data.count
    } catch (err) {
      if (current === requestId) error.value = getErrorMessage(err)
    } finally {
      if (current === requestId) loading.value = false
    }
  }

  function setFilters(values: Partial<F>): void {
    Object.assign(filters, values)
  }

  function resetFilters(): void {
    for (const key of Object.keys(filters)) delete (filters as ListParams)[key]
    Object.assign(filters, initialFilters)
  }

  watch(
    () => ({ ...filters }),
    () => {
      if (page.value !== 1) page.value = 1
      else void reload()
    },
    { deep: true },
  )
  watch(page, () => void reload())

  return { filters, page, pageSize, items, count, loading, error, reload, setFilters, resetFilters }
}
