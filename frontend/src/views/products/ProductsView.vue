<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'

import { productsApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import PageHeader from '@/components/PageHeader.vue'
import PaginationBar from '@/components/PaginationBar.vue'
import { usePaginatedList } from '@/composables/usePaginatedList'
import type { Product, ProductSummary } from '@/types/api'
import { openPrintWindow, saveBlob } from '@/utils/download'
import { getErrorMessage } from '@/utils/errors'
import { formatDateTime, formatNumber } from '@/utils/format'

const LOW_STOCK_BELOW = 5
const RED_ROW_BELOW = 3

const list = usePaginatedList<Product, { search: string; quantity_lt: number | null }>(
  productsApi.list,
  {
    search: '',
    quantity_lt: null,
  },
)
const summary = ref<ProductSummary | null>(null)
const actionError = ref<string | null>(null)
const exporting = ref<'excel' | 'pdf' | null>(null)

async function loadSummary(): Promise<void> {
  summary.value = await productsApi.summary({ ...list.filters }).catch(() => null)
}

async function remove(product: Product): Promise<void> {
  if (!window.confirm(`حذف المنتج ${product.name}؟`)) return
  try {
    await productsApi.remove(product.id)
    await Promise.all([list.reload(), loadSummary()])
  } catch (err) {
    actionError.value = getErrorMessage(err)
  }
}

async function exportFile(format: 'excel' | 'pdf'): Promise<void> {
  exporting.value = format
  try {
    const blob = await productsApi.export(format)
    saveBlob(blob, `products-${new Date().getFullYear()}.${format === 'excel' ? 'xlsx' : 'pdf'}`)
  } catch (err) {
    actionError.value = getErrorMessage(err)
  } finally {
    exporting.value = null
  }
}

function barcodeUrl(product: Product, print = false): string {
  return `/products/${product.id}/barcode${print ? '?print=1' : ''}`
}

watch(() => ({ ...list.filters }), loadSummary, { deep: true })
onMounted(() => {
  void list.reload()
  void loadSummary()
})
</script>

<template>
  <PageHeader title="المنتجات">
    <input
      v-model.lazy="list.filters.search"
      class="form-control"
      placeholder="بحث بالاسم او الكود"
      style="width: 14rem"
    />
    <button
      type="button"
      class="btn"
      :class="list.filters.quantity_lt ? 'btn-danger' : 'btn-outline-danger'"
      @click="list.filters.quantity_lt = list.filters.quantity_lt ? null : LOW_STOCK_BELOW"
    >
      المنتجات المقتربة من النفاذ
    </button>
    <button
      type="button"
      class="btn btn-outline-success"
      :disabled="exporting !== null"
      @click="exportFile('excel')"
    >
      <i class="bi bi-file-earmark-excel"></i> Excel
    </button>
    <button
      type="button"
      class="btn btn-outline-success"
      :disabled="exporting !== null"
      @click="exportFile('pdf')"
    >
      <i class="bi bi-file-earmark-pdf"></i> PDF
    </button>
    <RouterLink :to="{ name: 'product-create' }" class="btn btn-success"
      ><i class="bi bi-plus-lg"></i> اضافة</RouterLink
    >
  </PageHeader>

  <div v-if="summary" class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge text-bg-info fs-6 fw-normal"
      >عدد الاصناف: {{ formatNumber(summary.products_count) }}</span
    >
    <span class="badge text-bg-info fs-6 fw-normal"
      >اجمالي القطع: {{ formatNumber(summary.total_quantity) }}</span
    >
    <span class="badge text-bg-info fs-6 fw-normal"
      >اجمالي سعر الشراء: {{ formatNumber(summary.total_purchasing_price) }}</span
    >
    <span class="badge text-bg-info fs-6 fw-normal"
      >اجمالي سعر البيع: {{ formatNumber(summary.total_selling_price) }}</span
    >
    <span class="badge text-bg-success fs-6 fw-normal"
      >صافي مكسب: {{ formatNumber(summary.expected_profit) }}</span
    >
  </div>

  <AlertMessage :message="list.error.value ?? actionError" @close="actionError = null" />

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-nowrap mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>سعر الشراء</th>
            <th>سعر البيع</th>
            <th>الكمية</th>
            <th>سعر الشراء الكلى</th>
            <th>سعر البيع الكلى</th>
            <th>الكود</th>
            <th>المسؤول</th>
            <th>اخر تعديل</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(product, index) in list.items.value"
            :key="product.id"
            :class="{ 'table-danger': product.quantity < RED_ROW_BELOW }"
          >
            <td>{{ (list.page.value - 1) * list.pageSize + index + 1 }}</td>
            <td class="text-wrap">{{ product.name }}</td>
            <td>{{ formatNumber(product.purchasing_price) }}</td>
            <td>{{ formatNumber(product.selling_price) }}</td>
            <td>{{ product.quantity }}</td>
            <td>{{ formatNumber(product.total_purchasing_price) }}</td>
            <td>{{ formatNumber(product.total_selling_price) }}</td>
            <td>{{ product.code }}</td>
            <td>{{ product.created_by_name ?? '--' }}</td>
            <td class="ltr text-end">{{ formatDateTime(product.updated_at) }}</td>
            <td>
              <RouterLink
                :to="{ name: 'product-edit', params: { id: product.id } }"
                class="btn btn-sm btn-info"
                title="تعديل"
              >
                <i class="bi bi-pencil-square"></i>
              </RouterLink>
              <a
                :href="barcodeUrl(product)"
                target="_blank"
                class="btn btn-sm btn-secondary ms-1"
                title="عرض الباركود"
              >
                <i class="bi bi-upc"></i>
              </a>
              <button
                type="button"
                class="btn btn-sm btn-secondary ms-1"
                title="طباعة الباركود"
                @click="openPrintWindow(barcodeUrl(product, true))"
              >
                <i class="bi bi-printer"></i>
              </button>
              <button
                type="button"
                class="btn btn-sm btn-danger ms-1"
                title="حذف"
                @click="remove(product)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!list.loading.value && list.items.value.length === 0">
            <td colspan="11" class="text-center text-muted py-4">لا توجد منتجات</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white">
      <PaginationBar
        v-model:page="list.page.value"
        :page-size="list.pageSize"
        :count="list.count.value"
      />
    </div>
  </div>
</template>
