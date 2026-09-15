<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import { notesApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import PageHeader from '@/components/PageHeader.vue'
import PaginationBar from '@/components/PaginationBar.vue'
import { usePaginatedList } from '@/composables/usePaginatedList'
import { useAuthStore } from '@/stores/auth'
import type { Note } from '@/types/api'
import { getErrorMessage } from '@/utils/errors'
import { formatDateTime } from '@/utils/format'

const auth = useAuthStore()
const list = usePaginatedList<Note, { search: string }>(notesApi.list, { search: '' })
const newNote = ref('')
const saving = ref(false)
const error = ref<string | null>(null)

async function add(): Promise<void> {
  saving.value = true
  error.value = null
  try {
    await notesApi.create({ note: newNote.value.trim() })
    newNote.value = ''
    list.page.value = 1
    await list.reload()
  } catch (err) {
    error.value = getErrorMessage(err)
  } finally {
    saving.value = false
  }
}

async function remove(note: Note): Promise<void> {
  if (!window.confirm('حذف الملحوظة؟')) return
  try {
    await notesApi.remove(note.id)
    await list.reload()
  } catch (err) {
    error.value = getErrorMessage(err)
  }
}

onMounted(list.reload)
</script>

<template>
  <PageHeader title="الملحوظات">
    <input
      v-model.lazy="list.filters.search"
      class="form-control"
      placeholder="بحث"
      style="width: 14rem"
    />
  </PageHeader>

  <form class="card mb-3" @submit.prevent="add">
    <div class="card-body d-flex gap-2">
      <input v-model="newNote" class="form-control" placeholder="الملحوظة" required />
      <button type="submit" class="btn btn-info text-nowrap" :disabled="saving || !newNote.trim()">
        اضافة
      </button>
    </div>
  </form>

  <AlertMessage :message="list.error.value ?? error" @close="error = null" />

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>الملحوظة</th>
            <th>المسؤول</th>
            <th>التاريخ</th>
            <th v-if="auth.isMaster"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(note, index) in list.items.value" :key="note.id">
            <td>{{ (list.page.value - 1) * list.pageSize + index + 1 }}</td>
            <td style="white-space: pre-line">{{ note.note }}</td>
            <td>{{ note.created_by_name ?? '--' }}</td>
            <td class="ltr text-end text-nowrap">{{ formatDateTime(note.created_at) }}</td>
            <td v-if="auth.isMaster" class="text-nowrap">
              <RouterLink
                :to="{ name: 'note-edit', params: { id: note.id } }"
                class="btn btn-sm btn-info"
                title="تعديل"
              >
                <i class="bi bi-pencil-square"></i>
              </RouterLink>
              <button
                type="button"
                class="btn btn-sm btn-danger ms-1"
                title="حذف"
                @click="remove(note)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!list.loading.value && list.items.value.length === 0">
            <td colspan="5" class="text-center text-muted py-4">لا توجد ملحوظات</td>
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
