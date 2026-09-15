import { AxiosError, AxiosHeaders, type AxiosResponse } from 'axios'
import { describe, expect, it } from 'vitest'

import { getErrorMessage, getFieldErrors } from '@/utils/errors'

function apiError(status: number, data: unknown): AxiosError {
  const response = {
    status,
    data,
    headers: {},
    config: { headers: new AxiosHeaders() },
  } as AxiosResponse
  return new AxiosError('failed', 'ERR_BAD_REQUEST', undefined, undefined, response)
}

describe('getErrorMessage', () => {
  it('returns the API detail message', () => {
    const error = apiError(400, { detail: 'كود هذا المنتج غير صالح 99999', errors: null })

    expect(getErrorMessage(error)).toBe('كود هذا المنتج غير صالح 99999')
  })

  it('explains network failures', () => {
    expect(getErrorMessage(new AxiosError('Network Error'))).toContain('تعذر الاتصال')
  })
})

describe('getFieldErrors', () => {
  it('maps each field to its first message, including nested list errors', () => {
    const error = apiError(400, {
      detail: 'x',
      errors: { password: ['قصيرة', 'شائعة'], items: [{ code: ['مطلوب'] }] },
    })

    expect(getFieldErrors(error)).toEqual({ password: 'قصيرة', items: 'مطلوب' })
  })

  it('is empty for non API errors', () => {
    expect(getFieldErrors(new Error('boom'))).toEqual({})
  })
})
