import { isAxiosError } from 'axios'

import type { ApiErrorBody } from '@/types/api'

const NETWORK_ERROR = 'تعذر الاتصال بالخادم، تحقق من الاتصال وحاول مرة اخرى'
const UNKNOWN_ERROR = 'حدث خطأ غير متوقع'

function isApiErrorBody(value: unknown): value is ApiErrorBody {
  return typeof value === 'object' && value !== null && 'detail' in value
}

/** One human readable (Arabic) message for any failed request. */
export function getErrorMessage(error: unknown): string {
  if (isAxiosError(error)) {
    if (!error.response) return NETWORK_ERROR
    if (isApiErrorBody(error.response.data)) return String(error.response.data.detail)
    return UNKNOWN_ERROR
  }
  return error instanceof Error ? error.message : UNKNOWN_ERROR
}

function firstMessage(value: unknown): string {
  if (Array.isArray(value)) return value.length ? firstMessage(value[0]) : ''
  if (value && typeof value === 'object') {
    const first = Object.values(value)[0]
    return first === undefined ? '' : firstMessage(first)
  }
  return value === undefined || value === null ? '' : String(value)
}

/** Field name -> first validation message, for showing errors under form inputs. */
export function getFieldErrors(error: unknown): Record<string, string> {
  if (!isAxiosError(error) || !isApiErrorBody(error.response?.data)) return {}
  const errors = error.response.data.errors
  if (!errors) return {}
  return Object.fromEntries(
    Object.entries(errors).map(([field, value]) => [field, firstMessage(value)]),
  )
}
