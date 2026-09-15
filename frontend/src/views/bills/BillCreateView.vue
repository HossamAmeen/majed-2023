<script setup lang="ts">
import { isAxiosError } from 'axios'
import { computed, nextTick, onMounted, reactive, ref } from 'vue'

import { billsApi, productsApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import BillReceipt from '@/components/BillReceipt.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useShopStore } from '@/stores/shop'
import type { Bill } from '@/types/api'
import { billTotals, lineTotal } from '@/utils/billTotals'
import { openPrintWindow } from '@/utils/download'
import { getErrorMessage } from '@/utils/errors'
import { formatMoney, formatNumber } from '@/utils/format'

interface Line {
  code: number
  name: string
  price: number
  stock: number
  quantity: number
  discount: number
}

const shop = useShopStore()
const customer = reactive({ name: '', phone: '', discount: 0 })
const lines = ref<Line[]>([])
const scanCode = ref('')
const scanInput = ref<HTMLInputElement | null>(null)
const lookupMessage = ref<{ text: string; ok: boolean } | null>(null)
const looking = ref(false)
const submitting = ref(false)
const error = ref<string | null>(null)
const createdBill = ref<Bill | null>(null)

const totals = computed(() => billTotals(lines.value, Number(customer.discount) || 0))

function focusScanner(): void {
  void nextTick(() => scanInput.value?.focus())
}

async function addByCode(): Promise<void> {
  const code = scanCode.value.trim()
  if (!/^\d+$/.test(code) || looking.value) return
  looking.value = true
  try {
    const product = await productsApi.byCode(code)
    const existing = lines.value.find((line) => line.code === product.code)
    if (existing) {
      existing.quantity += 1
      existing.stock = product.quantity
    } else {
      lines.value.push({
        code: product.code,
        name: product.name,
        price: Number(product.selling_price),
        stock: product.quantity,
        quantity: 1,
        discount: 0,
      })
    }
    lookupMessage.value = {
      text: `المنتج: ${product.name} - السعر: ${formatMoney(product.selling_price)} - العدد المتاح: ${product.quantity}`,
      ok: true,
    }
    scanCode.value = ''
  } catch (err) {
    const notFound = isAxiosError(err) && err.response?.status === 404
    lookupMessage.value = {
      text: notFound ? `لا يوجد منتج بالكود ${code}` : getErrorMessage(err),
      ok: false,
    }
  } finally {
    looking.value = false
    focusScanner()
  }
}

function removeLine(index: number): void {
  lines.value.splice(index, 1)
  focusScanner()
}

function reset(): void {
  Object.assign(customer, { name: '', phone: '', discount: 0 })
  lines.value = []
  scanCode.value = ''
  lookupMessage.value = null
}

async function submit(): Promise<void> {
  error.value = null
  if (lines.value.length === 0) {
    error.value = 'لا يوجد منتجات في هذه الفاتوره'
    return
  }
  submitting.value = true
  try {
    createdBill.value = await billsApi.create({
      name: customer.name,
      phone: customer.phone,
      discount: Number(customer.discount) || 0,
      items: lines.value.map(({ code, quantity, discount }) => ({
        code,
        quantity: Number(quantity),
        discount: Number(discount) || 0,
      })),
    })
    reset()
  } catch (err) {
    error.value = getErrorMessage(err)
  } finally {
    submitting.value = false
    focusScanner()
  }
}

onMounted(() => {
  shop.load().catch(() => undefined)
  focusScanner()
})
</script>

<template>
  <PageHeader title="اضافة فاتورة" />

  <div class="row g-3">
    <div class="col-xl-8">
      <form class="card" @submit.prevent="submit">
        <div class="card-body">
          <AlertMessage :message="error" @close="error = null" />

          <div class="row g-2 mb-3">
            <div class="col-md-5">
              <label for="bill-name" class="form-label">اسم المشتري</label>
              <input id="bill-name" v-model="customer.name" class="form-control" />
            </div>
            <div class="col-md-4">
              <label for="bill-phone" class="form-label">رقم الموبايل</label>
              <input
                id="bill-phone"
                v-model="customer.phone"
                class="form-control"
                inputmode="tel"
              />
            </div>
            <div class="col-md-3">
              <label for="bill-discount" class="form-label">خصم على الفاتورة</label>
              <input
                id="bill-discount"
                v-model.number="customer.discount"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
              />
            </div>
          </div>

          <label for="scan" class="form-label"
            >كود المنتج (امسح الباركود او اكتب الكود ثم Enter)</label
          >
          <div class="input-group mb-2">
            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
            <input
              id="scan"
              ref="scanInput"
              v-model="scanCode"
              class="form-control form-control-lg"
              inputmode="numeric"
              autocomplete="off"
              @keydown.enter.prevent="addByCode"
            />
            <button
              type="button"
              class="btn btn-outline-primary"
              :disabled="looking"
              @click="addByCode"
            >
              اضافة
            </button>
          </div>
          <div
            v-if="lookupMessage"
            class="small mb-3"
            :class="lookupMessage.ok ? 'text-primary' : 'text-danger'"
          >
            {{ lookupMessage.text }}
          </div>

          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>المنتج</th>
                  <th>الكود</th>
                  <th>السعر</th>
                  <th style="width: 7rem">الكمية</th>
                  <th style="width: 7rem">الخصم</th>
                  <th>الاجمالي</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(line, index) in lines" :key="line.code">
                  <td>{{ line.name }}</td>
                  <td>{{ line.code }}</td>
                  <td>{{ formatNumber(line.price) }}</td>
                  <td>
                    <input
                      v-model.number="line.quantity"
                      type="number"
                      min="1"
                      class="form-control form-control-sm"
                      :class="{ 'is-invalid': line.quantity > line.stock }"
                      required
                    />
                    <div v-if="line.quantity > line.stock" class="invalid-feedback">
                      المتاح {{ line.stock }}
                    </div>
                  </td>
                  <td>
                    <input
                      v-model.number="line.discount"
                      type="number"
                      min="0"
                      step="0.01"
                      class="form-control form-control-sm"
                    />
                  </td>
                  <td>{{ formatNumber(lineTotal(line)) }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger"
                      title="حذف"
                      @click="removeLine(index)"
                    >
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="lines.length === 0">
                  <td colspan="7" class="text-center text-muted py-4">لم يتم اضافة منتجات بعد</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex flex-wrap gap-3 fw-semibold">
              <span>الاجمالي: {{ formatNumber(totals.subtotal) }}</span>
              <span class="text-danger">الخصم: {{ formatNumber(totals.totalDiscount) }}</span>
              <span class="fs-5 text-success">المطلوب: {{ formatMoney(totals.net) }}</span>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm"></span>
              حفظ الفاتورة
            </button>
          </div>
        </div>
      </form>
    </div>

    <div v-if="createdBill" class="col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="alert alert-success">تم الاضافه بنجاح - فاتورة رقم {{ createdBill.id }}</div>
          <BillReceipt :bill="createdBill" :shop="shop.configuration" />
          <div class="d-flex gap-2 mt-3">
            <button
              type="button"
              class="btn btn-info"
              @click="openPrintWindow(`/bills/${createdBill.id}/receipt?print=1`)"
            >
              <i class="bi bi-printer"></i> طباعة
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="createdBill = null">
              اغلاق
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
