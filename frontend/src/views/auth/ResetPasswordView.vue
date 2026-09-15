<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

import { authApi } from '@/api/auth'
import AlertMessage from '@/components/AlertMessage.vue'
import { getErrorMessage, getFieldErrors } from '@/utils/errors'

import AuthCard from './AuthCard.vue'

const route = useRoute()
const form = reactive({ password: '', password_confirmation: '' })
const success = ref<string | null>(null)
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})
const submitting = ref(false)

async function submit(): Promise<void> {
  submitting.value = true
  error.value = null
  fieldErrors.value = {}
  try {
    success.value = await authApi.confirmPasswordReset({
      uid: String(route.params.uid),
      token: String(route.params.token),
      ...form,
    })
  } catch (err) {
    error.value = getErrorMessage(err)
    fieldErrors.value = getFieldErrors(err)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthCard title="اعادة تعيين كلمة المرور">
    <template v-if="success">
      <AlertMessage :message="success" variant="success" @close="success = null" />
      <RouterLink :to="{ name: 'login' }" class="btn btn-primary w-100">تسجيل الدخول</RouterLink>
    </template>
    <form v-else @submit.prevent="submit">
      <AlertMessage :message="error" @close="error = null" />
      <div class="mb-3">
        <label for="password" class="form-label">كلمة المرور الجديدة</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="form-control"
          autocomplete="new-password"
          required
        />
        <div v-if="fieldErrors.password" class="invalid-feedback d-block">
          {{ fieldErrors.password }}
        </div>
      </div>
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">تاكيد كلمة المرور</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="form-control"
          autocomplete="new-password"
          required
        />
      </div>
      <button type="submit" class="btn btn-primary w-100" :disabled="submitting">حفظ</button>
    </form>
  </AuthCard>
</template>
