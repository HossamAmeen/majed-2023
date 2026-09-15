<script setup lang="ts">
defineProps<{ title: string }>()
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card w-100" style="max-width: 420px">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <img src="/4md.jpg" alt="" width="80" height="80" class="rounded-circle mb-2" />
          <h1 class="h5 mb-0">{{ title }}</h1>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>
