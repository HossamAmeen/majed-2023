<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { productsApi } from '@/api/resources'
import AlertMessage from '@/components/AlertMessage.vue'
import FormField from '@/components/FormField.vue'
import PageHeader from '@/components/PageHeader.vue'
import type { Product, ProductInput } from '@/types/api'
import { getErrorMessage, getFieldErrors } from '@/utils/errors'
import { formatNumber } from '@/utils/format'

const route = useRoute()
const router = useRouter()

const productId = computed(() => (route.params.id ? Number(route.params.id) : null))
const product = ref<Product | null>(null)
const form = reactive<ProductInput>({
  name: '',
  purchasing_price: 0,
  selling_price: 0,
  quantity: 0,
})
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})
const saving = ref(false)

const totalPurchasingPrice = computed(
  () => (Number(form.purchasing_price) || 0) * (Number(form.quantity) || 0),
)

onMounted(async () => {
  if (!productId.value) return
  try {
    product.value = await productsApi.get(productId.value)
    const { name, purchasing_price, selling_price, quantity } = product.value
    Object.assign(form, { name, purchasing_price, selling_price, quantity })
  } catch (err) {
    error.value = getErrorMessage(err)
  }
})

async function save(): Promise<void> {
  saving.value = true
  error.value = null
  fieldErrors.value = {}
  try {
    if (productId.value) await productsApi.update(productId.value, { ...form })
    else await productsApi.create({ ...form })
    await router.push({ name: 'products' })
  } catch (err) {
    error.value = getErrorMessage(err)
    fieldErrors.value = getFieldErrors(err)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <PageHeader
    :title="productId ? `تعديل المنتج ${product?.name ?? ''}` : 'اضافة منتج'"
    :subtitle="product ? `الكود: ${product.code}` : 'يتم توليد كود المنتج تلقائيا'"
  />
  <div class="card" style="max-width: 640px">
    <div class="card-body">
      <AlertMessage :message="error" @close="error = null" />
      <form @submit.prevent="save">
        <FormField id="name" label="اسم المنتج" :error="fieldErrors.name">
          <input id="name" v-model="form.name" class="form-control" required />
        </FormField>
        <div class="row">
          <div class="col-sm-6">
            <FormField
              id="purchasing_price"
              label="سعر الشراء"
              :error="fieldErrors.purchasing_price"
            >
              <input
                id="purchasing_price"
                v-model.number="form.purchasing_price"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                required
              />
            </FormField>
          </div>
          <div class="col-sm-6">
            <FormField id="selling_price" label="سعر البيع" :error="fieldErrors.selling_price">
              <input
                id="selling_price"
                v-model.number="form.selling_price"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                required
              />
            </FormField>
          </div>
        </div>
        <FormField id="quantity" label="الكمية" :error="fieldErrors.quantity">
          <input
            id="quantity"
            v-model.number="form.quantity"
            type="number"
            min="0"
            step="1"
            class="form-control"
            required
          />
        </FormField>
        <FormField id="total" label="سعر الشراء الكلى">
          <input
            id="total"
            :value="formatNumber(totalPurchasingPrice)"
            class="form-control"
            readonly
          />
        </FormField>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          {{ productId ? 'تعديل' : 'اضافة' }}
        </button>
      </form>
    </div>
  </div>
</template>
