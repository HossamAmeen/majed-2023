import { flushPromises } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'

import { usePaginatedList } from '@/composables/usePaginatedList'
import type { ListParams, Paginated } from '@/types/api'

function page(results: number[], count: number): Paginated<number> {
  return { count, next: null, previous: null, results }
}

describe('usePaginatedList', () => {
  it('sends limit/offset with the filters and stores the page', async () => {
    const fetchPage = vi.fn<(params: ListParams) => Promise<Paginated<number>>>(async () =>
      page([1, 2], 120),
    )
    const list = usePaginatedList(fetchPage, { search: '' }, 50)

    await list.reload()

    expect(fetchPage).toHaveBeenCalledWith({ search: '', limit: 50, offset: 0 })
    expect(list.items.value).toEqual([1, 2])
    expect(list.count.value).toBe(120)
  })

  it('goes back to the first page when a filter changes', async () => {
    const fetchPage = vi.fn<(params: ListParams) => Promise<Paginated<number>>>(async () =>
      page([], 0),
    )
    const list = usePaginatedList(fetchPage, { search: '' }, 50)
    list.page.value = 3
    await nextTick()
    await flushPromises()

    list.filters.search = 'قميص'
    await nextTick()
    await flushPromises()

    expect(list.page.value).toBe(1)
    expect(fetchPage).toHaveBeenLastCalledWith({ search: 'قميص', limit: 50, offset: 0 })
  })

  it('exposes the API error message', async () => {
    const list = usePaginatedList(async () => {
      throw new Error('انقطع الاتصال')
    }, {})

    await list.reload()

    expect(list.error.value).toBe('انقطع الاتصال')
    expect(list.loading.value).toBe(false)
  })
})
