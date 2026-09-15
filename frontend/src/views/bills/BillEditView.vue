<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { billsApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import PageHeader from '@/components/PageHeader.vue'
import type { Bill } from '@/types/api'
import { billTotals, lineTotal } from '@/utils/billTotals'
import { getErrorMessage } from '@/utils/errors'
import { formatMoney, formatNumber } from '@/utils/format'

const route = useRoute()
const router = useRouter()

const bill = ref<Bill | null>(null)
const form = reactive({ name: '', phone: '', discount: 0 })
const quantities = reactive<Record<number, number>>({})
const error = ref<string | null>(null)
const saving = ref(false)

const previewLines = computed(() =>
  (bill.value?.orders ?? []).map((order) => ({
    price: order.price,
    discount: order.discount,
    quantity: Number(quantities[order.id] ?? order.quantity),
  })),
)
const totals = computed(() => billTotals(previewLines.value, Number(form.discount) || 0))

onMounted(async () => {
  try {
    bill.value = await billsApi.get(String(route.params.id))
    Object.assign(form, {
      name: bill.value.name,
      phone: bill.value.phone,
      discount: bill.value.discount,
    })
    for (const order of bill.value.orders) quantities[order.id] = order.quantity
  } catch (err) {
    error.value = getErrorMessage(err)
  }
})

async function save(): Promise<void> {
  if (!bill.value) return
  const changed = bill.value.orders
    .filter((order) => Number(quantities[order.id]) !== order.quantity)
    .map((order) => ({ id: order.id, quantity: Number(quantities[order.id]) }))
  if (
    changed.some((line) => line.quantity === 0) &&
    !window.confirm('الكميات الصفرية سيتم استرجاعها الى المخزن. متابعة؟')
  ) {
    return
  }
  saving.value = true
  error.value = null
  try {
    await billsApi.update(bill.value.id, {
      name: form.name,
      phone: form.phone,
      discount: Number(form.discount) || 0,
      orders: changed,
    })
    await router.push({ name: 'bills' })
  } catch (err) {
    error.value = getErrorMessage(err)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <PageHeader :title="`تعديل بيانات الفاتورة رقم ${route.params.id}`" />
  <AlertMessage :message="error" @close="error = null" />

  <form v-if="bill" class="card" @submit.prevent="save">
    <div class="card-body">
      <div class="row g-2 mb-3">
        <div class="col-md-5">
          <label for="name" class="form-label">اسم المشتري</label>
          <input id="name" v-model="form.name" class="form-control" />
        </div>
        <div class="col-md-4">
          <label for="phone" class="form-label">رقم الموبايل</label>
          <input id="phone" v-model="form.phone" class="form-control" />
        </div>
        <div class="col-md-3">
          <label for="discount" class="form-label">خصم على الفاتورة</label>
          <input
            id="discount"
            v-model.number="form.discount"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
          />
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>المنتج</th>
              <th>الكود</th>
              <th>السعر</th>
              <th>الخصم</th>
              <th style="width: 8rem">الكمية</th>
              <th>الاجمالي</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(order, index) in bill.orders" :key="order.id">
              <td>{{ order.product_name }}</td>
              <td>{{ order.product_code ?? '--' }}</td>
              <td>{{ formatNumber(order.price) }}</td>
              <td>{{ formatNumber(order.discount) }}</td>
              <td>
                <input
                  v-model.number="quantities[order.id]"
                  type="number"
                  min="0"
                  class="form-control form-control-sm"
                  required
                />
              </td>
              <td>{{ formatNumber(lineTotal(previewLines[index]!)) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p class="text-muted small">اجعل الكمية 0 لاسترجاع المنتج بالكامل.</p>

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex flex-wrap gap-3 fw-semibold">
          <span>الاجمالي: {{ formatNumber(totals.subtotal) }}</span>
          <span class="text-danger">الخصم: {{ formatNumber(totals.totalDiscount) }}</span>
          <span class="text-success">المطلوب: {{ formatMoney(totals.net) }}</span>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="saving">تعديل</button>
      </div>
    </div>
  </form>
</template>
