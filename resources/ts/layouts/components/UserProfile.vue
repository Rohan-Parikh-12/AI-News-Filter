<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const userData  = useCookie<any>('userData')
</script>

<template>
  <VBadge
    v-if="userData"
    dot
    bordered
    location="bottom right"
    offset-x="2"
    offset-y="2"
    color="success"
    class="user-profile-badge"
  >
    <VAvatar
      class="cursor-pointer"
      size="38"
      :color="!(userData && userData.avatar) ? 'primary' : undefined"
      :variant="!(userData && userData.avatar) ? 'tonal' : undefined"
    >
      <VImg v-if="userData && userData.avatar" :src="userData.avatar" />
      <VIcon v-else icon="ri-user-line" />

      <VMenu activator="parent" width="230" location="bottom end" offset="15px">
        <VList>
          <!-- User info -->
          <VListItem class="px-4">
            <div class="d-flex gap-x-2 align-center">
              <VAvatar
                :color="!(userData && userData.avatar) ? 'primary' : undefined"
                :variant="!(userData && userData.avatar) ? 'tonal' : undefined"
              >
                <VImg v-if="userData && userData.avatar" :src="userData.avatar" />
                <VIcon v-else icon="ri-user-line" />
              </VAvatar>
              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  {{ userData.fullName || userData.username }}
                </div>
                <div class="text-capitalize text-caption text-disabled">
                  {{ userData.role }}
                </div>
              </div>
            </div>
          </VListItem>

          <VDivider class="my-1" />

          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <!-- Preferences -->
            <VListItem :to="{ name: 'preferences' }" class="px-4">
              <template #prepend>
                <VIcon icon="ri-settings-4-line" size="22" />
              </template>
              <VListItemTitle>Preferences</VListItemTitle>
            </VListItem>

            <VDivider class="my-1" />

            <!-- Logout -->
            <VListItem class="px-4">
              <VBtn
                block
                color="error"
                size="small"
                append-icon="ri-logout-box-r-line"
                @click="authStore.logout()"
              >
                Logout
              </VBtn>
            </VListItem>
          </PerfectScrollbar>
        </VList>
      </VMenu>
    </VAvatar>
  </VBadge>
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>
