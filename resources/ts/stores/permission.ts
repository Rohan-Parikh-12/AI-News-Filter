import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export interface PermissionItem {
  id: number
  name: string
  roles: string[]
}

export interface PermissionStatistic {
  total: number
  assigned: number
  roles: number
  modules: number
}

export const usePermissionStore = defineStore('permission', () => {
  const permissions = ref<PermissionItem[]>([])
  const totalPerms  = ref(0)
  const statistic   = ref<PermissionStatistic>({ total: 0, assigned: 0, roles: 0, modules: 0 })
  const loading     = ref(false)

  async function getPermissionData(params: Record<string, any> = {}) {
    loading.value = true
    try {
      const res = await $api('getPermissionData', { query: params })
      permissions.value = res.data
      totalPerms.value  = res.total
    }
    finally { loading.value = false }
  }

  async function permissionStatistic() {
    const res = await $api('permissionStatistic')
    statistic.value = res.data
  }

  async function managePermission(payload: { id?: number; name: string }) {
    const res = await $api('managePermission', { method: 'POST', body: payload })
    return res
  }

  async function permissionDelete(payload: { id?: number; ids?: number[] }) {
    const res = await $api('permissionDelete', { method: 'POST', body: payload })
    return res
  }

  return { permissions, totalPerms, statistic, loading, getPermissionData, permissionStatistic, managePermission, permissionDelete }
})
