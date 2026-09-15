/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** API root, e.g. https://shop.example.com/api/v1. Defaults to /api/v1 (same origin). */
  readonly VITE_API_BASE_URL?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
