<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { notesApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import PageHeader from '@/components/PageHeader.vue'
import { getErrorMessage } from '@/utils/errors'

const route = useRoute()
const router = useRouter()
const note = ref('')
const error = ref<string | null>(null)
const saving = ref(false)

onMounted(async () => {
  try {
    note.value = (await notesApi.get(String(route.params.id))).note
  } catch (err) {
    error.value = getErrorMessage(err)
  }
})

async function save(): Promise<void> {
  saving.value = true
  error.value = null
  try {
    await notesApi.update(String(route.params.id), { note: note.value })
    await router.push({ name: 'notes' })
  } catch (err) {
    error.value = getErrorMessage(err)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <PageHeader title="تعديل ملحوظة" />
  <div class="card" style="max-width: 640px">
    <div class="card-body">
      <AlertMessage :message="error" @close="error = null" />
      <form @submit.prevent="save">
        <textarea v-model="note" class="form-control mb-3" rows="4" required></textarea>
        <button type="submit" class="btn btn-primary" :disabled="saving">تعديل</button>
      </form>
    </div>
  </div>
</template>
