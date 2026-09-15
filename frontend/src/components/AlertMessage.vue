<script setup lang="ts">
defineProps<{ message: string | null; variant?: 'success' | 'danger' | 'warning' | 'info' }>()
const emit = defineEmits<{ close: [] }>()
</script>

<template>
  <div
    v-if="message"
    class="alert d-flex justify-content-between align-items-start"
    :class="`alert-${variant ?? 'danger'}`"
    role="alert"
  >
    <span>{{ message }}</span>
    <button type="button" class="btn-close" aria-label="اغلاق" @click="emit('close')"></button>
  </div>
</template>
