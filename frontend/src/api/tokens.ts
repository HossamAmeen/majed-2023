const ACCESS_KEY = 'shop.access'
const REFRESH_KEY = 'shop.refresh'

function read(key: string): string | null {
  try {
    return localStorage.getItem(key)
  } catch {
    return null
  }
}

export const tokens = {
  get access(): string | null {
    return read(ACCESS_KEY)
  },
  get refresh(): string | null {
    return read(REFRESH_KEY)
  },
  set(access: string, refresh?: string): void {
    localStorage.setItem(ACCESS_KEY, access)
    if (refresh) localStorage.setItem(REFRESH_KEY, refresh)
  },
  clear(): void {
    localStorage.removeItem(ACCESS_KEY)
    localStorage.removeItem(REFRESH_KEY)
  },
}
