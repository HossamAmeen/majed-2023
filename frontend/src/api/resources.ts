import type {
  Bill,
  BillCreateInput,
  BillUpdateInput,
  ListParams,
  Note,
  Order,
  OrderSummary,
  Paginated,
  Product,
  ProductInput,
  ProductLookup,
  ProductSummary,
  ShopConfiguration,
  Statistics,
  User,
  UserInput,
} from '@/types/api'

import { http } from './http'

function cleanParams(params: ListParams = {}): ListParams {
  return Object.fromEntries(
    Object.entries(params).filter(
      ([, value]) => value !== undefined && value !== null && value !== '',
    ),
  )
}

function crud<T, Input>(path: string) {
  return {
    async list(params?: ListParams): Promise<Paginated<T>> {
      const { data } = await http.get<Paginated<T>>(`${path}/`, { params: cleanParams(params) })
      return data
    },
    async get(id: number | string): Promise<T> {
      const { data } = await http.get<T>(`${path}/${id}/`)
      return data
    },
    async create(payload: Input): Promise<T> {
      const { data } = await http.post<T>(`${path}/`, payload)
      return data
    },
    async update(id: number | string, payload: Partial<Input>): Promise<T> {
      const { data } = await http.patch<T>(`${path}/${id}/`, payload)
      return data
    },
    async remove(id: number | string): Promise<void> {
      await http.delete(`${path}/${id}/`)
    },
  }
}

export const usersApi = crud<User, UserInput>('/users')

export const notesApi = crud<Note, { note: string }>('/notes')

export const productsApi = {
  ...crud<Product, ProductInput>('/products'),
  async summary(params?: ListParams): Promise<ProductSummary> {
    const { data } = await http.get<ProductSummary>('/products/summary/', {
      params: cleanParams(params),
    })
    return data
  },
  async byCode(code: string | number): Promise<ProductLookup> {
    const { data } = await http.get<ProductLookup>(`/products/by-code/${code}/`)
    return data
  },
  async export(format: 'excel' | 'pdf'): Promise<Blob> {
    const { data } = await http.get<Blob>(`/products/export/${format}/`, { responseType: 'blob' })
    return data
  },
}

const billsCrud = crud<Bill, BillCreateInput>('/bills')
export const billsApi = {
  list: billsCrud.list,
  get: billsCrud.get,
  create: billsCrud.create,
  remove: billsCrud.remove,
  async update(id: number | string, payload: BillUpdateInput): Promise<Bill> {
    const { data } = await http.patch<Bill>(`/bills/${id}/`, payload)
    return data
  },
}

export const ordersApi = {
  list: crud<Order, never>('/orders').list,
  remove: crud<Order, never>('/orders').remove,
  async summary(params?: ListParams): Promise<OrderSummary> {
    const { data } = await http.get<OrderSummary>('/orders/summary/', {
      params: cleanParams(params),
    })
    return data
  },
}

export const dashboardApi = {
  async configuration(): Promise<ShopConfiguration> {
    const { data } = await http.get<ShopConfiguration>('/configuration/')
    return data
  },
  async updateConfiguration(payload: Partial<ShopConfiguration>): Promise<ShopConfiguration> {
    const { data } = await http.patch<ShopConfiguration>('/configuration/', payload)
    return data
  },
  async statistics(): Promise<Statistics> {
    const { data } = await http.get<Statistics>('/statistics/')
    return data
  },
}
