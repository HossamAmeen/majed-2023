<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center gap-3">
    <h1 class="display-4">404</h1>
    <p class="text-muted">الصفحة غير موجودة</p>
    <RouterLink to="/" class="btn btn-primary">الرئيسية</RouterLink>
  </div>
</template>
