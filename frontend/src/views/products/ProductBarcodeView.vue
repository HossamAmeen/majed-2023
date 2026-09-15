<script setup lang="ts">
import JsBarcode from 'jsbarcode'
import { nextTick, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

import { productsApi } from '@/api/resources'
import type { Product } from '@/types/api'
import { getErrorMessage } from '@/utils/errors'
import { formatNumber } from '@/utils/format'

const route = useRoute()
const product = ref<Product | null>(null)
const error = ref<string | null>(null)
const barcode = ref<SVGSVGElement | null>(null)

function print(): void {
  window.print()
}

onMounted(async () => {
  try {
    product.value = await productsApi.get(String(route.params.id))
    await nextTick()
    if (barcode.value) {
      JsBarcode(barcode.value, String(product.value.code), {
        format: 'CODE39',
        width: 1.2,
        height: 30,
        margin: 0,
        displayValue: false,
      })
    }
    if (route.query.print) setTimeout(print, 300)
  } catch (err) {
    error.value = getErrorMessage(err)
  }
})
</script>

<template>
  <div class="label-page">
    <p v-if="error" class="text-danger">{{ error }}</p>
    <div v-if="product" class="label">
      <div class="label-name">{{ product.name }}</div>
      <svg ref="barcode"></svg>
      <div class="label-meta">
        <span>الكود {{ product.code }}</span>
        <span>السعر {{ formatNumber(product.selling_price) }} جنيه</span>
      </div>
    </div>
    <button type="button" class="btn btn-primary btn-sm mt-3 no-print" @click="print">اطبع</button>
  </div>
</template>

<style scoped>
.label-page {
  padding: 1rem;
  text-align: center;
  background: #fff;
  min-height: 100vh;
}

.label {
  display: inline-block;
  width: 50mm;
  font-size: 10px;
}

.label-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.label svg {
  width: 100%;
  height: 30px;
}

.label-meta {
  display: flex;
  justify-content: space-between;
}

@media print {
  @page {
    margin: 0;
  }

  .label-page {
    padding: 0;
    min-height: auto;
  }
}
</style>
