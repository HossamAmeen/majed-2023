import type { AccountInput, LoginResponse, User } from '@/types/api'

import { http } from './http'

export const authApi = {
  async login(login: string, password: string): Promise<LoginResponse> {
    const { data } = await http.post<LoginResponse>('/auth/login/', { login, password })
    return data
  },
  async logout(refresh: string): Promise<void> {
    await http.post('/auth/logout/', { refresh })
  },
  async me(): Promise<User> {
    const { data } = await http.get<User>('/auth/me/')
    return data
  },
  async updateMe(payload: Partial<AccountInput>): Promise<User> {
    const { data } = await http.patch<User>('/auth/me/', payload)
    return data
  },
  async requestPasswordReset(email: string): Promise<string> {
    const { data } = await http.post<{ detail: string }>('/auth/password-reset/', { email })
    return data.detail
  },
  async confirmPasswordReset(payload: {
    uid: string
    token: string
    password: string
    password_confirmation: string
  }): Promise<string> {
    const { data } = await http.post<{ detail: string }>('/auth/password-reset/confirm/', payload)
    return data.detail
  },
}
