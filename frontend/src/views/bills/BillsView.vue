<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import { billsApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import PageHeader from '@/components/PageHeader.vue'
import PaginationBar from '@/components/PaginationBar.vue'
import { usePaginatedList } from '@/composables/usePaginatedList'
import type { Bill } from '@/types/api'
import { openPrintWindow } from '@/utils/download'
import { getErrorMessage } from '@/utils/errors'
import { formatDateTime, formatNumber } from '@/utils/format'

const currentYear = new Date().getFullYear()
const list = usePaginatedList<Bill, { search: string; year: number | null }>(billsApi.list, {
  search: '',
  year: currentYear,
})
const actionError = ref<string | null>(null)

async function remove(bill: Bill): Promise<void> {
  if (!window.confirm(`حذف (استرجاع) الفاتورة رقم ${bill.id}؟ ستعود كل منتجاتها الى المخزن.`))
    return
  try {
    await billsApi.remove(bill.id)
    await list.reload()
  } catch (err) {
    actionError.value = getErrorMessage(err)
  }
}

onMounted(list.reload)
</script>

<template>
  <PageHeader
    title="الفواتير"
    :subtitle="list.filters.year ? `فواتير سنة ${list.filters.year}` : 'كل الفواتير'"
  >
    <input
      v-model.lazy="list.filters.search"
      class="form-control"
      placeholder="بحث بالاسم او الموبايل او رقم الفاتورة"
      style="width: 16rem"
    />
    <button
      type="button"
      class="btn btn-outline-secondary"
      @click="list.filters.year = list.filters.year ? null : currentYear"
    >
      {{ list.filters.year ? 'الكل' : 'هذه السنة' }}
    </button>
    <RouterLink :to="{ name: 'bill-create' }" class="btn btn-success"
      ><i class="bi bi-plus-lg"></i> فاتورة جديدة</RouterLink
    >
  </PageHeader>

  <AlertMessage :message="list.error.value ?? actionError" @close="actionError = null" />

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead class="table-nowrap">
          <tr>
            <th>رقم الفاتورة</th>
            <th>اسم المشتري</th>
            <th>رقم الموبايل</th>
            <th>المنتج (العدد) - السعر</th>
            <th>فلوس الفاتورة</th>
            <th>خصم الفاتورة</th>
            <th>بعد الخصم</th>
            <th>التاريخ</th>
            <th>المسؤول</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="bill in list.items.value" :key="bill.id">
            <td>{{ bill.id }}</td>
            <td>{{ bill.name || '--' }}</td>
            <td>{{ bill.phone || '--' }}</td>
            <td>
              <div v-for="order in bill.orders" :key="order.id" class="small">
                {{ order.product_name }} ({{ order.quantity }}) -
                {{ formatNumber(order.price) }} جنيها
              </div>
            </td>
            <td>{{ formatNumber(bill.subtotal) }}</td>
            <td>{{ formatNumber(bill.total_discount) }}</td>
            <td class="fw-semibold">{{ formatNumber(bill.net_total) }}</td>
            <td class="ltr text-end text-nowrap">{{ formatDateTime(bill.created_at) }}</td>
            <td>{{ bill.created_by_name ?? '--' }}</td>
            <td class="text-nowrap">
              <RouterLink
                :to="{ name: 'bill-edit', params: { id: bill.id } }"
                class="btn btn-sm btn-info"
                title="تعديل"
              >
                <i class="bi bi-pencil-square"></i>
              </RouterLink>
              <a
                :href="`/bills/${bill.id}/receipt`"
                target="_blank"
                class="btn btn-sm btn-success ms-1"
                title="عرض الفاتورة"
              >
                <i class="bi bi-eye"></i>
              </a>
              <button
                type="button"
                class="btn btn-sm btn-secondary ms-1"
                title="طباعة"
                @click="openPrintWindow(`/bills/${bill.id}/receipt?print=1`)"
              >
                <i class="bi bi-printer"></i>
              </button>
              <button
                type="button"
                class="btn btn-sm btn-danger ms-1"
                title="حذف"
                @click="remove(bill)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!list.loading.value && list.items.value.length === 0">
            <td colspan="10" class="text-center text-muted py-4">لا توجد فواتير</td>
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
