<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { usersApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import FormField from '@/components/FormField.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useAuthStore } from '@/stores/auth'
import { ROLE, type User, type UserInput } from '@/types/api'
import { getErrorMessage, getFieldErrors } from '@/utils/errors'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const userId = computed(() => (route.params.id ? Number(route.params.id) : null))
const editing = ref<User | null>(null)
const form = reactive<UserInput>({
  user_name: '',
  full_name: '',
  email: '',
  phone: '',
  role: ROLE.EMPLOYEE,
  password: '',
  password_confirmation: '',
})
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})
const saving = ref(false)

/** Same rule as the API: you cannot change your own role, and the master's role is fixed. */
const canEditRole = computed(
  () =>
    !editing.value || (editing.value.id !== auth.user?.id && editing.value.role !== ROLE.MASTER),
)

onMounted(async () => {
  if (!userId.value) return
  try {
    editing.value = await usersApi.get(userId.value)
    const { user_name, full_name, email, phone, role } = editing.value
    Object.assign(form, { user_name, full_name, email, phone, role })
  } catch (err) {
    error.value = getErrorMessage(err)
  }
})

async function save(): Promise<void> {
  error.value = null
  fieldErrors.value = {}
  if (form.password !== form.password_confirmation) {
    fieldErrors.value = { password_confirmation: 'كلمتا المرور غير متطابقتين' }
    return
  }
  const payload: Partial<UserInput> = { ...form }
  if (!canEditRole.value) delete payload.role
  saving.value = true
  try {
    if (userId.value) await usersApi.update(userId.value, payload)
    else await usersApi.create(payload as UserInput)
    await router.push({ name: 'users' })
  } catch (err) {
    error.value = getErrorMessage(err)
    fieldErrors.value = getFieldErrors(err)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <PageHeader :title="userId ? `تعديل المستخدم ${editing?.user_name ?? ''}` : 'اضافة مستخدم'" />
  <div class="card" style="max-width: 640px">
    <div class="card-body">
      <AlertMessage :message="error" @close="error = null" />
      <form @submit.prevent="save">
        <FormField id="user_name" label="اسم المستخدم" :error="fieldErrors.user_name">
          <input id="user_name" v-model="form.user_name" class="form-control" required />
        </FormField>
        <FormField id="email" label="البريد" :error="fieldErrors.email">
          <input id="email" v-model="form.email" type="email" class="form-control" required />
        </FormField>
        <FormField id="full_name" label="الاسم بالكامل" :error="fieldErrors.full_name">
          <input id="full_name" v-model="form.full_name" class="form-control" required />
        </FormField>
        <FormField id="phone" label="الموبايل" :error="fieldErrors.phone">
          <input id="phone" v-model="form.phone" class="form-control" />
        </FormField>
        <FormField
          id="password"
          :label="userId ? 'كلمة السر (اتركها فارغة لعدم التغيير)' : 'كلمة السر'"
          :error="fieldErrors.password"
        >
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control"
            autocomplete="new-password"
            :required="!userId"
          />
        </FormField>
        <FormField
          id="password_confirmation"
          label="تاكيد كلمة السر"
          :error="fieldErrors.password_confirmation"
        >
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="form-control"
            autocomplete="new-password"
            :required="!userId"
          />
        </FormField>
        <fieldset v-if="canEditRole" class="mb-3">
          <legend class="form-label fs-6">الصلاحية</legend>
          <div class="form-check form-check-inline">
            <input
              id="role-admin"
              v-model="form.role"
              class="form-check-input"
              type="radio"
              :value="ROLE.ADMIN"
            />
            <label class="form-check-label" for="role-admin">مسؤول</label>
          </div>
          <div class="form-check form-check-inline">
            <input
              id="role-employee"
              v-model="form.role"
              class="form-check-input"
              type="radio"
              :value="ROLE.EMPLOYEE"
            />
            <label class="form-check-label" for="role-employee">موظف</label>
          </div>
          <div v-if="fieldErrors.role" class="invalid-feedback d-block">{{ fieldErrors.role }}</div>
        </fieldset>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          {{ userId ? 'تعديل' : 'اضافة' }}
        </button>
      </form>
    </div>
  </div>
</template>
