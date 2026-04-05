import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export interface RoleItem {
  id: number
  name: string
  permissions: string[]
  users_count: number
}

export interface RoleStatistic {
  total: number
  with_users: number
  permissions: number
  users: number
}

export const useRoleStore = defineStore('role', () => {
  const roles          = ref<RoleItem[]>([])
  const totalRoles     = ref(0)
  const allPermissions = ref<string[]>([])
  const statistic      = ref<RoleStatistic>({ total: 0, with_users: 0, permissions: 0, users: 0 })
  const loading        = ref(false)

  async function getRoleData(params: Record<string, any> = {}) {
    loading.value = true
    try {
      const res = await $api('getRoleData', { query: params })
      roles.value      = res.data
      totalRoles.value = res.total
    }
    finally { loading.value = false }
  }

  async function roleStatistic() {
    const res = await $api('roleStatistic')
    statistic.value = res.data
  }

  async function getAllPermissions() {
    const res = await $api('getAllPermissions')
    allPermissions.value = res.data
  }

  async function manageRole(payload: { id?: number; name: string; permissions: string[] }) {
    const res = await $api('manageRole', { method: 'POST', body: payload })
    return res
  }

  async function roleDelete(payload: { id?: number; ids?: number[] }) {
    const res = await $api('roleDelete', { method: 'POST', body: payload })
    return res
  }

  return { roles, totalRoles, allPermissions, statistic, loading, getRoleData, roleStatistic, getAllPermissions, manageRole, roleDelete }
})
