<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ page: number; pageSize: number; count: number }>()
const emit = defineEmits<{ 'update:page': [page: number] }>()

const pages = computed(() => Math.max(1, Math.ceil(props.count / props.pageSize)))
const from = computed(() => (props.count === 0 ? 0 : (props.page - 1) * props.pageSize + 1))
const to = computed(() => Math.min(props.page * props.pageSize, props.count))
</script>

<template>
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 no-print">
    <small class="text-muted">عرض {{ from }} - {{ to }} من {{ count }}</small>
    <div v-if="pages > 1" class="btn-group btn-group-sm" dir="ltr">
      <button
        type="button"
        class="btn btn-outline-secondary"
        :disabled="page >= pages"
        @click="emit('update:page', page + 1)"
      >
        التالي
      </button>
      <span class="btn btn-outline-secondary disabled">{{ page }} / {{ pages }}</span>
      <button
        type="button"
        class="btn btn-outline-secondary"
        :disabled="page <= 1"
        @click="emit('update:page', page - 1)"
      >
        السابق
      </button>
    </div>
  </div>
</template>
