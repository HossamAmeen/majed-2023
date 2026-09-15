<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

import { billsApi } from '@/api/resources'
import BillReceipt from '@/components/BillReceipt.vue'
import { useShopStore } from '@/stores/shop'
import type { Bill } from '@/types/api'
import { getErrorMessage } from '@/utils/errors'

const route = useRoute()
const shop = useShopStore()
const bill = ref<Bill | null>(null)
const error = ref<string | null>(null)

function print(): void {
  window.print()
}

onMounted(async () => {
  try {
    const [loaded] = await Promise.all([billsApi.get(String(route.params.id)), shop.load()])
    bill.value = loaded
    if (route.query.print) setTimeout(print, 300)
  } catch (err) {
    error.value = getErrorMessage(err)
  }
})
</script>

<template>
  <div class="bg-white min-vh-100 p-2">
    <p v-if="error" class="text-danger">{{ error }}</p>
    <template v-if="bill">
      <BillReceipt :bill="bill" :shop="shop.configuration" />
      <button type="button" class="btn btn-info btn-sm mt-3 no-print" @click="print">طباعة</button>
    </template>
  </div>
</template>
