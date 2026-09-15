<script setup lang="ts">
defineProps<{
  label: string
  value: string | number
  icon: string
  tone?: 'primary' | 'success' | 'danger' | 'warning'
}>()
</script>

<template>
  <div class="card h-100">
    <div class="card-body d-flex align-items-center gap-3">
      <div
        class="rounded-3 d-flex align-items-center justify-content-center fs-4"
        :class="`bg-${tone ?? 'primary'}-subtle text-${tone ?? 'primary'}`"
        style="width: 3rem; height: 3rem"
      >
        <i class="bi" :class="icon"></i>
      </div>
      <div>
        <div class="fs-5 fw-bold" :class="tone === 'danger' ? 'text-danger' : ''">{{ value }}</div>
        <div class="text-muted small">{{ label }}</div>
      </div>
    </div>
  </div>
</template>
