import { defineStore } from 'pinia'
import { ref } from 'vue'

import { dashboardApi } from '@/api/resources'
import type { ShopConfiguration } from '@/types/api'

export const useShopStore = defineStore('shop', () => {
  const configuration = ref<ShopConfiguration | null>(null)

  async function load(force = false): Promise<ShopConfiguration> {
    if (!configuration.value || force) {
      configuration.value = await dashboardApi.configuration()
    }
    return configuration.value
  }

  function set(value: ShopConfiguration): void {
    configuration.value = value
  }

  return { configuration, load, set }
})
