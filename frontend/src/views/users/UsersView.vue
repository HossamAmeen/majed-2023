<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import { usersApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import PageHeader from '@/components/PageHeader.vue'
import PaginationBar from '@/components/PaginationBar.vue'
import { usePaginatedList } from '@/composables/usePaginatedList'
import { useAuthStore } from '@/stores/auth'
import type { User } from '@/types/api'
import { getErrorMessage } from '@/utils/errors'
import { formatDateTime } from '@/utils/format'

const auth = useAuthStore()
const list = usePaginatedList<User, { search: string }>(usersApi.list, { search: '' })
const actionError = ref<string | null>(null)

async function remove(user: User): Promise<void> {
  if (!window.confirm(`حذف المستخدم ${user.user_name}؟`)) return
  try {
    await usersApi.remove(user.id)
    await list.reload()
  } catch (err) {
    actionError.value = getErrorMessage(err)
  }
}

onMounted(list.reload)
</script>

<template>
  <PageHeader title="المستخدمين">
    <input
      v-model.lazy="list.filters.search"
      class="form-control"
      placeholder="بحث"
      style="width: 14rem"
    />
    <RouterLink :to="{ name: 'user-create' }" class="btn btn-success"
      ><i class="bi bi-plus-lg"></i> اضافة</RouterLink
    >
  </PageHeader>

  <AlertMessage :message="list.error.value ?? actionError" @close="actionError = null" />

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>اسم المستخدم</th>
            <th>الاسم</th>
            <th>البريد</th>
            <th>الصلاحية</th>
            <th>اخر دخول</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in list.items.value" :key="user.id">
            <td>{{ (list.page.value - 1) * list.pageSize + index + 1 }}</td>
            <td>{{ user.user_name }}</td>
            <td>{{ user.full_name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.role_display }}</td>
            <td class="ltr text-end">{{ formatDateTime(user.last_login) }}</td>
            <td class="text-nowrap">
              <RouterLink
                :to="{ name: 'user-edit', params: { id: user.id } }"
                class="btn btn-sm btn-info"
                title="تعديل"
              >
                <i class="bi bi-pencil-square"></i>
              </RouterLink>
              <button
                v-if="user.id !== auth.user?.id"
                type="button"
                class="btn btn-sm btn-danger ms-1"
                title="حذف"
                @click="remove(user)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!list.loading.value && list.items.value.length === 0">
            <td colspan="7" class="text-center text-muted py-4">لا يوجد مستخدمين</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white">
      <PaginationBar
        v-model:page="list.page.value"
        :page-size="list.pageSize"
        :count="list.count.value"
      />
    </div>
  </div>
</template>
