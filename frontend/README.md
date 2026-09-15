# 4MD Shop frontend

Vue 3 + TypeScript single-page app for the shop API (Arabic, RTL, Bootstrap 5).
Setup, environment variables and deployment are in the [root README](../README.md).

```bash
npm ci
npm run dev          # http://localhost:5173, proxies /api to http://127.0.0.1:8000
npm run test:unit    # vitest
npm run type-check   # vue-tsc
npm run lint         # oxlint + eslint (with --fix)
npm run format       # prettier
npm run build        # type-check + production build into dist/
```

Layout:

| Path | Contents |
|---|---|
| `src/api/http.ts` | Axios client: adds the JWT, refreshes it once on 401 |
| `src/api/resources.ts` | Typed functions for every endpoint |
| `src/types/api.ts` | API response and request types |
| `src/stores/auth.ts` | Signed-in user and role checks |
| `src/router/index.ts` | Routes; `meta.roles` limits a page to roles, `meta.blank` hides the sidebar |
| `src/views/` | Screens: bills (POS, list, edit, receipt), orders, products, users, notes, dashboard, account |
| `src/composables/usePaginatedList.ts` | Shared filters and pagination for list pages |

Hiding a page in the router or sidebar is only for convenience; the API enforces permissions.
