import axios, { type AxiosError, type InternalAxiosRequestConfig } from 'axios'

import { tokens } from './tokens'

export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/v1'

export const http = axios.create({ baseURL: API_BASE_URL })

http.interceptors.request.use((config) => {
  const access = tokens.access
  if (access) config.headers.Authorization = `Bearer ${access}`
  return config
})

type RetriableConfig = InternalAxiosRequestConfig & { _retried?: boolean }

let refreshing: Promise<string> | null = null
let onSessionExpired: () => void = () => {}

/** Called when the refresh token is no longer valid (e.g. to redirect to the login page). */
export function setSessionExpiredHandler(handler: () => void): void {
  onSessionExpired = handler
}

async function refreshAccessToken(): Promise<string> {
  const refresh = tokens.refresh
  if (!refresh) throw new Error('No refresh token')
  // A bare axios call, so this request does not go through the interceptors below.
  const { data } = await axios.post<{ access: string; refresh?: string }>(
    `${API_BASE_URL}/auth/refresh/`,
    { refresh },
  )
  tokens.set(data.access, data.refresh)
  return data.access
}

http.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const config = error.config as RetriableConfig | undefined
    const isAuthCall =
      config?.url?.startsWith('/auth/login') || config?.url?.startsWith('/auth/refresh')

    if (
      error.response?.status !== 401 ||
      !config ||
      config._retried ||
      isAuthCall ||
      !tokens.refresh
    ) {
      return Promise.reject(error)
    }

    config._retried = true
    try {
      refreshing ??= refreshAccessToken().finally(() => {
        refreshing = null
      })
      const access = await refreshing
      config.headers.Authorization = `Bearer ${access}`
      return http(config)
    } catch {
      tokens.clear()
      onSessionExpired()
      return Promise.reject(error)
    }
  },
)
