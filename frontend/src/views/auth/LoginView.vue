<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'

import AlertMessage from '@/components/AlertMessage.vue'
import { useAuthStore } from '@/stores/auth'
import { getErrorMessage } from '@/utils/errors'

import AuthCard from './AuthCard.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({ login: '', password: '' })
const error = ref<string | null>(null)
const submitting = ref(false)

async function submit(): Promise<void> {
  submitting.value = true
  error.value = null
  try {
    await auth.login(form.login.trim(), form.password)
    const next = route.query.next
    const isInternal = typeof next === 'string' && next.startsWith('/') && !next.startsWith('//')
    await router.replace(isInternal ? next : '/')
  } catch (err) {
    error.value = getErrorMessage(err)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthCard title="تسجيل الدخول">
    <AlertMessage :message="error" @close="error = null" />
    <form @submit.prevent="submit">
      <div class="mb-3">
        <label for="login" class="form-label">اسم المستخدم او البريد الالكتروني</label>
        <input
          id="login"
          v-model="form.login"
          class="form-control"
          autocomplete="username"
          required
          autofocus
        />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">كلمة المرور</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="form-control"
          autocomplete="current-password"
          required
        />
      </div>
      <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
        <span v-if="submitting" class="spinner-border spinner-border-sm"></span>
        دخول
      </button>
      <div class="text-center mt-3">
        <RouterLink :to="{ name: 'forgot-password' }">نسيت كلمة المرور؟</RouterLink>
      </div>
    </form>
  </AuthCard>
</template>
