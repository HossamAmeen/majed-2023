<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import { useShopStore } from '@/stores/shop'
import { ROLE, type Role } from '@/types/api'

interface NavItem {
  to: string
  label: string
  icon: string
  roles?: readonly Role[]
}

const NAV_ITEMS: NavItem[] = [
  {
    to: '/dashboard',
    label: 'احصائيات السستم',
    icon: 'bi-bar-chart',
    roles: [ROLE.MASTER, ROLE.ADMIN],
  },
  { to: '/bills/new', label: 'فاتورة جديدة', icon: 'bi-cart-plus' },
  { to: '/bills', label: 'الفواتير', icon: 'bi-receipt' },
  { to: '/orders', label: 'الطلبات', icon: 'bi-bag-check' },
  { to: '/products', label: 'المنتجات', icon: 'bi-box-seam', roles: [ROLE.MASTER] },
  { to: '/users', label: 'المستخدمين', icon: 'bi-people', roles: [ROLE.MASTER] },
  { to: '/notes', label: 'ملحوظات', icon: 'bi-journal-text' },
  { to: '/account', label: 'تعديل بيانات الحساب', icon: 'bi-person-gear' },
]

const auth = useAuthStore()
const shop = useShopStore()
const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(false)

const items = computed(() => NAV_ITEMS.filter((item) => auth.hasRole(item.roles)))

watch(
  () => route.fullPath,
  () => {
    sidebarOpen.value = false
  },
)

onMounted(() => {
  shop.load().catch(() => undefined)
})

async function logout(): Promise<void> {
  await auth.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <div class="app-shell">
    <aside class="app-sidebar no-print p-3" :class="{ open: sidebarOpen }">
      <div class="text-center mb-4">
        <img src="/4md.jpg" alt="" width="84" height="84" class="rounded-circle mb-2 bg-white" />
        <div class="fw-bold text-white">{{ shop.configuration?.title ?? '4MD' }}</div>
        <small v-if="auth.user">{{ auth.user.full_name }} - {{ auth.user.role_display }}</small>
      </div>
      <nav class="nav flex-column gap-1">
        <RouterLink
          v-for="item in items"
          :key="item.to"
          :to="item.to"
          class="nav-link"
          :class="{ 'router-link-active': route.path === item.to }"
          active-class=""
          exact-active-class=""
        >
          <i class="bi" :class="item.icon"></i>
          <span>{{ item.label }}</span>
        </RouterLink>
        <button type="button" class="nav-link btn btn-link text-start" @click="logout">
          <i class="bi bi-power"></i>
          <span>تسجيل الخروج</span>
        </button>
      </nav>
    </aside>

    <div
      v-if="sidebarOpen"
      class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-lg-none"
      style="z-index: 1030"
      @click="sidebarOpen = false"
    ></div>

    <header
      class="app-topbar no-print align-items-center justify-content-between bg-white shadow-sm px-3 py-2"
    >
      <button
        type="button"
        class="btn btn-outline-secondary btn-sm"
        aria-label="القائمة"
        @click="sidebarOpen = true"
      >
        <i class="bi bi-list"></i>
      </button>
      <span class="fw-bold">{{ shop.configuration?.title ?? '4MD' }}</span>
    </header>

    <main class="app-main">
      <slot />
    </main>
  </div>
</template>
