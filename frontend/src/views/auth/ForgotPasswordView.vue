<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

import { authApi } from '@/api/auth'
import AlertMessage from '@/components/AlertMessage.vue'
import { getErrorMessage } from '@/utils/errors'

import AuthCard from './AuthCard.vue'

const email = ref('')
const success = ref<string | null>(null)
const error = ref<string | null>(null)
const submitting = ref(false)

async function submit(): Promise<void> {
  submitting.value = true
  error.value = null
  success.value = null
  try {
    success.value = await authApi.requestPasswordReset(email.value.trim())
  } catch (err) {
    error.value = getErrorMessage(err)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthCard title="نسيت كلمة المرور">
    <AlertMessage :message="success" variant="success" @close="success = null" />
    <AlertMessage :message="error" @close="error = null" />
    <form @submit.prevent="submit">
      <div class="mb-3">
        <label for="email" class="form-label">البريد الالكتروني</label>
        <input id="email" v-model="email" type="email" class="form-control" required autofocus />
      </div>
      <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
        ارسال رابط اعادة التعيين
      </button>
      <div class="text-center mt-3">
        <RouterLink :to="{ name: 'login' }">العودة لتسجيل الدخول</RouterLink>
      </div>
    </form>
  </AuthCard>
</template>
