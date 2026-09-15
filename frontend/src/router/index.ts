import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import { ROLE, type Role } from '@/types/api'

declare module 'vue-router' {
  interface RouteMeta {
    title?: string
    /** Reachable without signing in. */
    public?: boolean
    /** Render without the sidebar (login, printable pages). */
    blank?: boolean
    roles?: readonly Role[]
  }
}

const MANAGERS = [ROLE.MASTER, ROLE.ADMIN] as const
const MASTER_ONLY = [ROLE.MASTER] as const

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { title: 'تسجيل الدخول', public: true, blank: true },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { title: 'نسيت كلمة المرور', public: true, blank: true },
  },
  {
    path: '/reset-password/:uid/:token',
    name: 'reset-password',
    component: () => import('@/views/auth/ResetPasswordView.vue'),
    meta: { title: 'اعادة تعيين كلمة المرور', public: true, blank: true },
  },
  { path: '/', name: 'home', component: { render: () => null } },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { title: 'احصائيات السستم', roles: MANAGERS },
  },
  {
    path: '/account',
    name: 'account',
    component: () => import('@/views/AccountView.vue'),
    meta: { title: 'تعديل بيانات الحساب' },
  },
  {
    path: '/users',
    name: 'users',
    component: () => import('@/views/users/UsersView.vue'),
    meta: { title: 'المستخدمين', roles: MASTER_ONLY },
  },
  {
    path: '/users/new',
    name: 'user-create',
    component: () => import('@/views/users/UserFormView.vue'),
    meta: { title: 'اضافة مستخدم', roles: MASTER_ONLY },
  },
  {
    path: '/users/:id/edit',
    name: 'user-edit',
    component: () => import('@/views/users/UserFormView.vue'),
    meta: { title: 'تعديل مستخدم', roles: MASTER_ONLY },
  },
  {
    path: '/products',
    name: 'products',
    component: () => import('@/views/products/ProductsView.vue'),
    meta: { title: 'المنتجات', roles: MASTER_ONLY },
  },
  {
    path: '/products/new',
    name: 'product-create',
    component: () => import('@/views/products/ProductFormView.vue'),
    meta: { title: 'اضافة منتج', roles: MASTER_ONLY },
  },
  {
    path: '/products/:id/edit',
    name: 'product-edit',
    component: () => import('@/views/products/ProductFormView.vue'),
    meta: { title: 'تعديل منتج', roles: MASTER_ONLY },
  },
  {
    path: '/products/:id/barcode',
    name: 'product-barcode',
    component: () => import('@/views/products/ProductBarcodeView.vue'),
    meta: { title: 'باركود المنتج', roles: MASTER_ONLY, blank: true },
  },
  {
    path: '/orders',
    name: 'orders',
    component: () => import('@/views/orders/OrdersView.vue'),
    meta: { title: 'الطلبات' },
  },
  {
    path: '/bills',
    name: 'bills',
    component: () => import('@/views/bills/BillsView.vue'),
    meta: { title: 'الفواتير' },
  },
  {
    path: '/bills/new',
    name: 'bill-create',
    component: () => import('@/views/bills/BillCreateView.vue'),
    meta: { title: 'فاتورة جديدة' },
  },
  {
    path: '/bills/:id/edit',
    name: 'bill-edit',
    component: () => import('@/views/bills/BillEditView.vue'),
    meta: { title: 'تعديل فاتورة' },
  },
  {
    path: '/bills/:id/receipt',
    name: 'bill-receipt',
    component: () => import('@/views/bills/BillReceiptView.vue'),
    meta: { title: 'فاتورة', blank: true },
  },
  {
    path: '/notes',
    name: 'notes',
    component: () => import('@/views/notes/NotesView.vue'),
    meta: { title: 'ملحوظات' },
  },
  {
    path: '/notes/:id/edit',
    name: 'note-edit',
    component: () => import('@/views/notes/NoteEditView.vue'),
    meta: { title: 'تعديل ملحوظة', roles: MASTER_ONLY },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
    meta: { title: 'الصفحة غير موجودة', public: true, blank: true },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  await auth.ensureLoaded()

  if (to.meta.public) {
    return to.name === 'login' && auth.isAuthenticated ? { name: 'home' } : true
  }
  if (!auth.isAuthenticated) {
    return { name: 'login', query: { next: to.fullPath } }
  }
  if (to.name === 'home') {
    return { name: auth.canSeeStatistics ? 'dashboard' : 'bill-create' }
  }
  if (!auth.hasRole(to.meta.roles)) {
    return { name: 'home' }
  }
  return true
})

router.afterEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} | 4MD` : '4MD'
})

export default router
