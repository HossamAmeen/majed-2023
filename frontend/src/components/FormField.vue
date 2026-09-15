<script setup lang="ts">
defineProps<{ label: string; error?: string; id: string }>()
</script>

<template>
  <div class="mb-3">
    <label :for="id" class="form-label">{{ label }}</label>
    <slot />
    <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
  </div>
</template>
