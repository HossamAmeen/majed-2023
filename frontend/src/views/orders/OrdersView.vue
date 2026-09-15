<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'

import { ordersApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import PageHeader from '@/components/PageHeader.vue'
import PaginationBar from '@/components/PaginationBar.vue'
import { usePaginatedList } from '@/composables/usePaginatedList'
import type { Order, OrderSummary } from '@/types/api'
import { getErrorMessage } from '@/utils/errors'
import { formatDate, formatMoney, formatNumber, toISODate } from '@/utils/format'

interface OrderFilters {
  [key: string]: string | number | boolean | null
  returned: boolean | null
  date: string | null
  date_from: string | null
  year: number | null
  month: number | null
  search: string
}

const EMPTY: OrderFilters = {
  returned: null,
  date: null,
  date_from: null,
  year: null,
  month: null,
  search: '',
}

const list = usePaginatedList<Order, OrderFilters>(ordersApi.list, { ...EMPTY })
const summary = ref<OrderSummary | null>(null)
const actionError = ref<string | null>(null)

function applyPeriod(period: 'day' | 'month' | 'year', returned = false): void {
  const now = new Date()
  list.setFilters({
    ...EMPTY,
    returned: returned || null,
    date: period === 'day' ? toISODate(now) : null,
    year: period === 'day' ? null : now.getFullYear(),
    month: period === 'month' ? now.getMonth() + 1 : null,
  })
}

async function loadSummary(): Promise<void> {
  summary.value = await ordersApi.summary({ ...list.filters }).catch(() => null)
}

async function returnOrder(order: Order): Promise<void> {
  if (!window.confirm(`استرجاع ${order.product_name} (${order.quantity}) الى المخزن؟`)) return
  try {
    await ordersApi.remove(order.id)
    await Promise.all([list.reload(), loadSummary()])
  } catch (err) {
    actionError.value = getErrorMessage(err)
  }
}

watch(() => ({ ...list.filters }), loadSummary, { deep: true })
onMounted(() => {
  void list.reload()
  void loadSummary()
})
</script>

<template>
  <PageHeader title="الطلبات" :subtitle="list.filters.returned ? 'المرتجعات' : 'المبيعات'" />

  <div class="card mb-3 no-print">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
      <button
        type="button"
        class="btn btn-outline-secondary"
        @click="list.setFilters({ ...EMPTY })"
      >
        الكل
      </button>
      <button type="button" class="btn btn-info" @click="applyPeriod('day')">طلبات اليوم</button>
      <button type="button" class="btn btn-info" @click="applyPeriod('month')">طلبات الشهر</button>
      <button type="button" class="btn btn-info" @click="applyPeriod('year')">طلبات السنة</button>
      <span class="vr mx-1"></span>
      <button type="button" class="btn btn-danger" @click="applyPeriod('day', true)">
        مرتجعات اليوم
      </button>
      <button type="button" class="btn btn-danger" @click="applyPeriod('month', true)">
        مرتجعات الشهر
      </button>
      <button type="button" class="btn btn-danger" @click="applyPeriod('year', true)">
        مرتجعات السنة
      </button>
      <span class="vr mx-1"></span>
      <label class="d-flex align-items-center gap-2">
        من تاريخ
        <input
          v-model.lazy="list.filters.date_from"
          type="date"
          class="form-control"
          style="width: 11rem"
        />
      </label>
      <input
        v-model.lazy="list.filters.search"
        class="form-control"
        placeholder="بحث بالمنتج او العميل"
        style="width: 14rem"
      />
    </div>
  </div>

  <AlertMessage :message="list.error.value ?? actionError" @close="actionError = null" />

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-nowrap mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>المنتج</th>
            <th>كود المنتج</th>
            <th>السعر</th>
            <th>الخصم</th>
            <th>الكمية</th>
            <th>الاجمالي</th>
            <th>التاريخ</th>
            <th v-if="list.filters.returned" class="text-danger">تاريخ الاسترجاع</th>
            <th>صاحب الفاتورة (رقم الفاتورة)</th>
            <th>المسؤول</th>
            <th v-if="!list.filters.returned"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(order, index) in list.items.value" :key="order.id">
            <td>{{ (list.page.value - 1) * list.pageSize + index + 1 }}</td>
            <td class="text-wrap">{{ order.product_name }}</td>
            <td
              :class="{ 'text-danger': order.product_deleted }"
              :title="order.product_deleted ? 'منتج محذوف' : ''"
            >
              {{ order.product_code ?? '--' }}
            </td>
            <td>{{ formatNumber(order.price) }}</td>
            <td>{{ formatNumber(order.discount) }}</td>
            <td>{{ order.quantity }}</td>
            <td>{{ formatNumber(order.line_total) }}</td>
            <td class="ltr text-end">{{ formatDate(order.date) }}</td>
            <td v-if="list.filters.returned" class="ltr text-end text-danger">
              {{ formatDate(order.deleted_at) }}
            </td>
            <td>{{ order.bill_name || '--' }} ({{ order.bill_id ?? '--' }})</td>
            <td>{{ order.created_by_name ?? '--' }}</td>
            <td v-if="!list.filters.returned">
              <button
                type="button"
                class="btn btn-sm btn-danger"
                title="استرجاع"
                @click="returnOrder(order)"
              >
                <i class="bi bi-arrow-return-right"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!list.loading.value && list.items.value.length === 0">
            <td colspan="12" class="text-center text-muted py-4">لا توجد طلبات</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white">
      <div v-if="summary" class="d-flex flex-wrap gap-4 justify-content-center mb-2 fw-semibold">
        <span>اجمالي الفلوس: {{ formatMoney(summary.total_money) }}</span>
        <span>اجمالي الكمية: {{ formatNumber(summary.total_quantity) }}</span>
        <span>عدد الطلبات: {{ formatNumber(summary.orders_count) }}</span>
      </div>
      <PaginationBar
        v-model:page="list.page.value"
        :page-size="list.pageSize"
        :count="list.count.value"
      />
    </div>
  </div>
</template>
