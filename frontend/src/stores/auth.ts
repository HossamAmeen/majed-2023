import { isAxiosError } from 'axios'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import { authApi } from '@/api/auth'
import { tokens } from '@/api/tokens'
import { ROLE, type Role, type User } from '@/types/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  let loading: Promise<void> | null = null

  const isAuthenticated = computed(() => user.value !== null)
  const role = computed<Role | null>(() => user.value?.role ?? null)
  const isMaster = computed(() => role.value === ROLE.MASTER)
  const canSeeStatistics = computed(() => role.value === ROLE.MASTER || role.value === ROLE.ADMIN)

  function hasRole(roles?: readonly Role[]): boolean {
    return !roles || (role.value !== null && roles.includes(role.value))
  }

  /** Restore the session from stored tokens once per page load. */
  async function ensureLoaded(): Promise<void> {
    if (user.value || !tokens.access) return
    loading ??= authApi
      .me()
      .then((me) => {
        user.value = me
      })
      .catch((error: unknown) => {
        // Only forget the session when the server rejects it, not on a network hiccup.
        if (isAxiosError(error) && error.response?.status === 401) tokens.clear()
      })
      .finally(() => {
        loading = null
      })
    await loading
  }

  async function login(login: string, password: string): Promise<User> {
    const response = await authApi.login(login, password)
    tokens.set(response.access, response.refresh)
    user.value = response.user
    return response.user
  }

  async function logout(): Promise<void> {
    const refresh = tokens.refresh
    if (refresh) await authApi.logout(refresh).catch(() => undefined)
    tokens.clear()
    user.value = null
  }

  function forgetSession(): void {
    tokens.clear()
    user.value = null
  }

  return {
    user,
    role,
    isAuthenticated,
    isMaster,
    canSeeStatistics,
    hasRole,
    ensureLoaded,
    login,
    logout,
    forgetSession,
  }
})
