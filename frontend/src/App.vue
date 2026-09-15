<script setup lang="ts">
import { RouterView, useRoute } from 'vue-router'

import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
</script>

<template>
  <!-- Nothing renders until the first navigation resolves, so the layout never mounts for guests. -->
  <template v-if="route.matched.length === 0"></template>
  <RouterView v-else-if="route.meta.blank" />
  <AppLayout v-else>
    <RouterView />
  </AppLayout>
</template>
