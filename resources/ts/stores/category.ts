import { defineStore } from 'pinia'
import { $api } from '@/utils/api'
import type { Category, CategoryStatistic } from '@/types/category'

export const useCategoryStore = defineStore('category', () => {
  const categories  = ref<Category[]>([])
  const total       = ref(0)
  const statistic   = ref<CategoryStatistic>({ total: 0, active: 0, inactive: 0, archive: 0 })
  const loading     = ref(false)

  async function getCategoryData(params: Record<string, any> = {}) {
    loading.value = true
    try {
      const res = await $api('getCategoryData', { query: params })
      categories.value = res.data
      total.value      = res.total
    }
    finally { loading.value = false }
  }

  async function getCategoryDetails(id: number) {
    return await $api('getCategoryDetails', { query: { id } })
  }

  async function manageCategory(payload: Record<string, any>) {
    return await $api('manageCategory', { method: 'POST', body: payload })
  }

  async function categoryDelete(payload: { id?: number; ids?: number[] }) {
    return await $api('categoryDelete', { method: 'POST', body: payload })
  }

  async function categoryStatusChange(id: number, newStatus: '0' | '1' | '2') {
    return await $api('categoryStatusChange', { method: 'POST', body: { id, newStatus } })
  }

  async function categoryStatistic() {
    const res = await $api('categoryStatistic')
    statistic.value = res.data
  }

  return { categories, total, statistic, loading, getCategoryData, getCategoryDetails, manageCategory, categoryDelete, categoryStatusChange, categoryStatistic }
})
