<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

import { authApi } from '@/api/auth'
import AlertMessage from '@/components/AlertMessage.vue'
import FormField from '@/components/FormField.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useAuthStore } from '@/stores/auth'
import type { AccountInput } from '@/types/api'
import { getErrorMessage, getFieldErrors } from '@/utils/errors'

const auth = useAuthStore()
const form = reactive<AccountInput>({
  user_name: '',
  full_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})
const success = ref<string | null>(null)
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})
const saving = ref(false)

onMounted(() => {
  if (auth.user) {
    const { user_name, full_name, email, phone } = auth.user
    Object.assign(form, { user_name, full_name, email, phone })
  }
})

async function save(): Promise<void> {
  success.value = null
  error.value = null
  fieldErrors.value = {}
  if (form.password !== form.password_confirmation) {
    fieldErrors.value = { password_confirmation: 'كلمتا المرور غير متطابقتين' }
    return
  }
  saving.value = true
  try {
    auth.user = await authApi.updateMe({ ...form })
    form.password = ''
    form.password_confirmation = ''
    success.value = 'تم التحديث بنجاح'
  } catch (err) {
    error.value = getErrorMessage(err)
    fieldErrors.value = getFieldErrors(err)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <PageHeader
    :title="`تعديل بيانات ${auth.user?.role_display ?? ''} ${auth.user?.user_name ?? ''}`"
  />
  <div class="card" style="max-width: 640px">
    <div class="card-body">
      <AlertMessage :message="success" variant="success" @close="success = null" />
      <AlertMessage :message="error" @close="error = null" />
      <form @submit.prevent="save">
        <FormField id="user_name" label="اسم المستخدم" :error="fieldErrors.user_name">
          <input id="user_name" v-model="form.user_name" class="form-control" required />
        </FormField>
        <FormField id="full_name" label="الاسم بالكامل" :error="fieldErrors.full_name">
          <input id="full_name" v-model="form.full_name" class="form-control" required />
        </FormField>
        <FormField id="email" label="البريد الالكتروني" :error="fieldErrors.email">
          <input id="email" v-model="form.email" type="email" class="form-control" required />
        </FormField>
        <FormField id="phone" label="الموبايل" :error="fieldErrors.phone">
          <input id="phone" v-model="form.phone" class="form-control" />
        </FormField>
        <FormField
          id="password"
          label="كلمة المرور الجديدة (اتركها فارغة لعدم التغيير)"
          :error="fieldErrors.password"
        >
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control"
            autocomplete="new-password"
          />
        </FormField>
        <FormField
          id="password_confirmation"
          label="تاكيد كلمة المرور"
          :error="fieldErrors.password_confirmation"
        >
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="form-control"
            autocomplete="new-password"
          />
        </FormField>
        <button type="submit" class="btn btn-primary" :disabled="saving">تعديل</button>
      </form>
    </div>
  </div>
</template>
