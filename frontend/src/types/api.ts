export const ROLE = { MASTER: 1, ADMIN: 2, EMPLOYEE: 3 } as const
export type Role = (typeof ROLE)[keyof typeof ROLE]

export interface Paginated<T> {
  count: number
  next: string | null
  previous: string | null
  results: T[]
}

export interface ApiErrorBody {
  detail: string
  errors: Record<string, unknown> | null
}

export interface User {
  id: number
  user_name: string
  full_name: string
  email: string
  phone: string
  role: Role
  role_display: string
  last_login: string | null
  created_at: string
}

export interface UserInput {
  user_name: string
  full_name: string
  email: string
  phone: string
  role?: Role
  password?: string
  password_confirmation?: string
}

export type AccountInput = Omit<UserInput, 'role'>

export interface LoginResponse {
  access: string
  refresh: string
  user: User
}

export interface Product {
  id: number
  name: string
  code: number
  purchasing_price: number
  selling_price: number
  quantity: number
  total_purchasing_price: number
  total_selling_price: number
  created_by_name: string | null
  updated_by_name: string | null
  created_at: string
  updated_at: string
}

export interface ProductInput {
  name: string
  purchasing_price: number
  selling_price: number
  quantity: number
}

export interface ProductLookup {
  id: number
  code: number
  name: string
  selling_price: number
  quantity: number
}

export interface ProductSummary {
  products_count: number
  total_quantity: number
  total_purchasing_price: number
  total_selling_price: number
  expected_profit: number
}

export interface BillOrder {
  id: number
  product_id: number | null
  product_code: number | null
  product_name: string
  product_price: number
  price: number
  quantity: number
  discount: number
  line_total: number
  date: string
}

export interface Bill {
  id: number
  name: string
  phone: string
  discount: number
  subtotal: number
  orders_discount: number
  total_discount: number
  net_total: number
  orders: BillOrder[]
  created_by_name: string | null
  updated_by_name: string | null
  created_at: string
  updated_at: string
  deleted_at: string | null
}

export interface BillItemInput {
  code: number
  quantity: number
  discount: number
  price?: number | null
}

export interface BillCreateInput {
  name: string
  phone: string
  discount: number
  items: BillItemInput[]
}

export interface BillUpdateInput {
  name?: string
  phone?: string
  discount?: number
  orders?: { id: number; quantity: number }[]
}

export interface Order {
  id: number
  bill_id: number | null
  bill_name: string | null
  product_id: number | null
  product_code: number | null
  product_deleted: boolean
  product_name: string
  product_price: number
  price: number
  quantity: number
  discount: number
  line_total: number
  date: string
  status: string
  created_by_name: string | null
  created_at: string
  deleted_at: string | null
}

export interface OrderSummary {
  orders_count: number
  total_quantity: number
  total_money: number
}

export interface Note {
  id: number
  note: string
  created_by_name: string | null
  updated_by_name: string | null
  created_at: string
  updated_at: string
}

export interface ShopConfiguration {
  title: string
  phone: string
  address: string
  email: string
  receipt_header: string
  receipt_footer: string
  updated_at?: string
}

export interface PeriodStatistics {
  orders_count: number
  orders_money: number
}

export interface DayStatistics extends PeriodStatistics {
  orders_discount: number
  bills_discount: number
  total_discount: number
  goods_cost: number
  net_profit: number
}

export interface TodayStatistics extends DayStatistics {
  returned_count: number
  returned_money: number
}

export interface Statistics {
  date: string
  today: TodayStatistics
  yesterday: DayStatistics
  month: PeriodStatistics
  year: PeriodStatistics
  out_of_stock_products: number
}

export type ListParams = Record<string, string | number | boolean | undefined | null>
