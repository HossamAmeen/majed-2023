<script setup lang="ts">
import type { Bill, ShopConfiguration } from '@/types/api'
import { formatDateTime, formatNumber } from '@/utils/format'

defineProps<{ bill: Bill; shop: ShopConfiguration | null }>()
</script>

<template>
  <div class="receipt" dir="rtl">
    <h3 class="text-center">فـاتــورة</h3>
    <h3 class="text-center">
      {{ shop?.title }}
      <br />
      <img src="/4md.jpg" width="25" height="25" alt="" />
      <br />
      <span class="ltr">{{ formatDateTime(bill.created_at) }}</span>
    </h3>
    <p v-if="shop?.receipt_header" class="bold">{{ shop.receipt_header }}</p>
    <p>
      رقم الفاتورة : {{ bill.id }}<br />
      اسم العميل : {{ bill.name }}<br />
      تليفون : {{ bill.phone }}
    </p>
    <table>
      <thead>
        <tr>
          <th class="description">المنتج</th>
          <th class="quantity">الكمية</th>
          <th class="price">السعر</th>
          <th class="price-off">الخصم</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in bill.orders" :key="order.id">
          <td class="description">{{ order.product_name }}</td>
          <td class="quantity">{{ order.quantity }}</td>
          <td class="price">{{ formatNumber(order.price) }}</td>
          <td class="price-off">{{ formatNumber(order.discount) }}</td>
        </tr>
      </tbody>
    </table>
    <p class="bold">
      اجمالى السعر : {{ formatNumber(bill.subtotal) }}<br />
      اجمالى الخصم : {{ formatNumber(bill.total_discount) }}<br />
      المطلوب دفعه : {{ formatNumber(bill.net_total) }}
    </p>
    <p v-if="shop?.receipt_footer" class="bold">{{ shop.receipt_footer }}</p>
    <p v-if="shop?.address" class="bold">العنوان : {{ shop.address }}</p>
    <p v-if="shop?.phone" class="bold">تليفون : {{ shop.phone }}</p>
  </div>
</template>

<style scoped>
.receipt {
  width: 280px;
  font-family: 'Times New Roman', serif;
  font-size: 12px;
  color: #000;
  text-align: right;
}

.receipt h3 {
  font-size: 14px;
  font-weight: bold;
}

.receipt table {
  width: 100%;
  border-collapse: collapse;
}

.receipt th,
.receipt td {
  border-top: 1px solid #000;
  text-align: right;
  word-break: break-all;
}

.description {
  width: 150px;
}

.quantity {
  width: 40px;
}

.price {
  width: 50px;
}

.bold {
  font-weight: bold;
}

@media print {
  .receipt {
    width: 58mm;
  }
}
</style>
