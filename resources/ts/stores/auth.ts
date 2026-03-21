import { defineStore } from 'pinia'
import { useAbility } from '@casl/vue'
import type { Rule } from '@/plugins/casl/ability'

interface UserData {
  id: number
  name: string
  email: string
  role: string
  fullName: string
  username: string
  avatar: string | null
}

export const useAuthStore = defineStore('auth', () => {
  const ability = useAbility()
  const router  = useRouter()

  const userData         = useCookie<UserData | null>('userData')
  const accessToken      = useCookie<string | null>('accessToken')
  const userAbilityRules = useCookie<Rule[]>('userAbilityRules')

  const isLoggedIn = computed(() => !!(userData.value && accessToken.value))

  function _persist(token: string, user: UserData, rules: Rule[]) {
    accessToken.value      = token
    userData.value         = user
    userAbilityRules.value = rules
    ability.update(rules)
  }

  function _clear() {
    accessToken.value      = null
    userData.value         = null
    userAbilityRules.value = []
    ability.update([])
  }

  async function login(email: string, password: string) {
    const res = await $api('/auth/login', {
      method: 'POST',
      body: { email, password },
    })
    _persist(res.accessToken, res.userData, res.userAbilityRules)
    await router.replace('/')
  }

  async function register(name: string, email: string, password: string, passwordConfirmation: string) {
    const res = await $api('/auth/register', {
      method: 'POST',
      body: { name, email, password, password_confirmation: passwordConfirmation },
    })
    _persist(res.accessToken, res.userData, res.userAbilityRules)
    await router.replace('/')
  }

  async function logout() {
    await $api('/auth/logout', { method: 'POST' }).catch(() => {})
    _clear()
    await router.replace('/login')
  }

  async function fetchUser() {
    const res = await $api('/auth/me')
    userData.value         = res.userData
    userAbilityRules.value = res.userAbilityRules
    ability.update(res.userAbilityRules)
  }

  function has_permission(permission: string): boolean {
    return ability.can('manage', 'all') || ability.can(permission as any, permission as any)
  }

  return { userData, accessToken, isLoggedIn, login, register, logout, fetchUser, has_permission }
})
