<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'

import { dashboardApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import FormField from '@/components/FormField.vue'
import PageHeader from '@/components/PageHeader.vue'
import StatCard from '@/components/StatCard.vue'
import { useShopStore } from '@/stores/shop'
import type { ShopConfiguration, Statistics } from '@/types/api'
import { getErrorMessage, getFieldErrors } from '@/utils/errors'
import { formatMoney, formatNumber } from '@/utils/format'

type Tone = 'primary' | 'success' | 'danger' | 'warning'
interface Card {
  label: string
  value: string
  icon: string
  tone?: Tone
}

const shop = useShopStore()
const stats = ref<Statistics | null>(null)
const statsError = ref<string | null>(null)
const form = reactive<ShopConfiguration>({
  title: '',
  phone: '',
  address: '',
  email: '',
  receipt_header: '',
  receipt_footer: '',
})
const saving = ref(false)
const saved = ref<string | null>(null)
const formError = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})

const sections = computed<{ title: string; cards: Card[] }[]>(() => {
  const s = stats.value
  if (!s) return []
  return [
    {
      title: 'اليوم',
      cards: [
        { label: 'عدد طلبات اليوم', value: formatNumber(s.today.orders_count), icon: 'bi-cart' },
        {
          label: 'فلوس طلبات اليوم',
          value: formatMoney(s.today.orders_money),
          icon: 'bi-cash-stack',
          tone: 'success',
        },
        {
          label: 'مجموع خصومات اليوم',
          value: formatMoney(s.today.total_discount),
          icon: 'bi-percent',
          tone: 'danger',
        },
        { label: 'فلوس بضاعة اليوم', value: formatMoney(s.today.goods_cost), icon: 'bi-box' },
        {
          label: 'صافي الربح اليوم',
          value: formatMoney(s.today.net_profit),
          icon: 'bi-graph-up-arrow',
          tone: 'success',
        },
        {
          label: 'عدد المرتجعات اليوم',
          value: formatNumber(s.today.returned_count),
          icon: 'bi-arrow-return-right',
          tone: 'warning',
        },
        {
          label: 'فلوس المرتجعات اليوم',
          value: formatMoney(s.today.returned_money),
          icon: 'bi-arrow-counterclockwise',
          tone: 'warning',
        },
      ],
    },
    {
      title: 'الامس',
      cards: [
        {
          label: 'عدد طلبات الامس',
          value: formatNumber(s.yesterday.orders_count),
          icon: 'bi-cart',
        },
        {
          label: 'فلوس طلبات الامس',
          value: formatMoney(s.yesterday.orders_money),
          icon: 'bi-cash-stack',
          tone: 'success',
        },
        {
          label: 'مجموع خصومات الامس',
          value: formatMoney(s.yesterday.total_discount),
          icon: 'bi-percent',
          tone: 'danger',
        },
        { label: 'فلوس بضاعة الامس', value: formatMoney(s.yesterday.goods_cost), icon: 'bi-box' },
        {
          label: 'صافي الربح الامس',
          value: formatMoney(s.yesterday.net_profit),
          icon: 'bi-graph-up-arrow',
          tone: 'success',
        },
      ],
    },
    {
      title: 'الشهر والسنة',
      cards: [
        {
          label: 'طلبات هذا الشهر',
          value: formatNumber(s.month.orders_count),
          icon: 'bi-calendar-month',
        },
        {
          label: 'فلوس طلبات هذا الشهر',
          value: formatMoney(s.month.orders_money),
          icon: 'bi-cash-coin',
          tone: 'success',
        },
        {
          label: 'طلبات هذه السنة',
          value: formatNumber(s.year.orders_count),
          icon: 'bi-calendar3',
        },
        {
          label: 'فلوس طلبات هذه السنة',
          value: formatMoney(s.year.orders_money),
          icon: 'bi-cash-coin',
          tone: 'success',
        },
        {
          label: 'عدد المنتجات المنتهية',
          value: formatNumber(s.out_of_stock_products),
          icon: 'bi-exclamation-triangle',
          tone: 'danger',
        },
      ],
    },
  ]
})

async function loadStatistics(): Promise<void> {
  statsError.value = null
  try {
    stats.value = await dashboardApi.statistics()
  } catch (err) {
    statsError.value = getErrorMessage(err)
  }
}

async function loadConfiguration(): Promise<void> {
  try {
    Object.assign(form, await shop.load(true))
  } catch (err) {
    formError.value = getErrorMessage(err)
  }
}

async function save(): Promise<void> {
  saving.value = true
  saved.value = null
  formError.value = null
  fieldErrors.value = {}
  try {
    shop.set(await dashboardApi.updateConfiguration({ ...form }))
    saved.value = 'تم التحديث بنجاح'
  } catch (err) {
    formError.value = getErrorMessage(err)
    fieldErrors.value = getFieldErrors(err)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void loadStatistics()
  void loadConfiguration()
})
</script>

<template>
  <PageHeader title="احصائيات السستم" :subtitle="stats ? `بتاريخ ${stats.date}` : undefined">
    <button type="button" class="btn btn-outline-primary" @click="loadStatistics">
      <i class="bi bi-arrow-clockwise"></i> تحديث
    </button>
  </PageHeader>

  <AlertMessage :message="statsError" @close="statsError = null" />

  <div v-if="!stats && !statsError" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
  </div>

  <section v-for="section in sections" :key="section.title" class="mb-4">
    <h2 class="h6 text-muted mb-2">{{ section.title }}</h2>
    <div class="row g-3">
      <div v-for="card in section.cards" :key="card.label" class="col-12 col-sm-6 col-xl-3">
        <StatCard v-bind="card" />
      </div>
    </div>
  </section>

  <div class="card">
    <div class="card-body">
      <h2 class="h5 mb-3">بيانات المحل</h2>
      <AlertMessage :message="saved" variant="success" @close="saved = null" />
      <AlertMessage :message="formError" @close="formError = null" />
      <form class="row" @submit.prevent="save">
        <div class="col-md-4">
          <FormField id="title" label="اسم المحل" :error="fieldErrors.title">
            <input id="title" v-model="form.title" class="form-control" required />
          </FormField>
        </div>
        <div class="col-md-4">
          <FormField id="phone" label="الهاتف" :error="fieldErrors.phone">
            <input id="phone" v-model="form.phone" class="form-control" />
          </FormField>
        </div>
        <div class="col-md-4">
          <FormField id="address" label="العنوان" :error="fieldErrors.address">
            <input id="address" v-model="form.address" class="form-control" />
          </FormField>
        </div>
        <div class="col-md-6">
          <FormField
            id="receipt_header"
            label="بيانات السجل التجاري (تظهر في الفاتورة)"
            :error="fieldErrors.receipt_header"
          >
            <input id="receipt_header" v-model="form.receipt_header" class="form-control" />
          </FormField>
        </div>
        <div class="col-md-6">
          <FormField
            id="receipt_footer"
            label="سياسة الاستبدال والاسترجاع"
            :error="fieldErrors.receipt_footer"
          >
            <textarea
              id="receipt_footer"
              v-model="form.receipt_footer"
              class="form-control"
              rows="2"
            ></textarea>
          </FormField>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary" :disabled="saving">تحديث</button>
        </div>
      </form>
    </div>
  </div>
</template>
