<script setup lang="ts">
defineProps<{ title: string; subtitle?: string }>()
</script>

<template>
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h1 class="h4 mb-0">{{ title }}</h1>
      <small v-if="subtitle" class="text-muted">{{ subtitle }}</small>
    </div>
    <div class="d-flex flex-wrap gap-2 no-print">
      <slot />
    </div>
  </div>
</template>
