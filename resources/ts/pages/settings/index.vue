<script setup lang="ts">
import { VForm } from 'vuetify/components/VForm'
import { useRoleStore } from '@/stores/role'
import { usePermissionStore } from '@/stores/permission'
import { paginationMeta } from '@/utils/paginationMeta'
import poseM from '@images/pages/pose_m1.png'

definePage({ meta: { action: 'manage', subject: 'Settings' } })

const roleStore = useRoleStore()
const permStore = usePermissionStore()

// ── Tabs ───────────────────────────────────────────────────────────────────
const activeTab = ref('roles-permissions')

// ── Permission groups (for role dialog matrix) ─────────────────────────────
const GROUPS = [
  { label: 'Categories',  perms: ['categories-view', 'categories-create', 'categories-edit', 'categories-delete', 'categories-status'] },
  { label: 'Articles',    perms: ['articles-view', 'articles-save'] },
  { label: 'Digest',      perms: ['digest-view', 'digest-settings'] },
  { label: 'Preferences', perms: ['preferences-view', 'preferences-edit'] },
]

function permLabel(p: string) {
  return p.split('-').map((w: string) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
}

// ── Role Dialog (AddEditRoleDialog pattern) ────────────────────────────────
const isRoleDialogVisible    = ref(false)
const isAddRoleDialogVisible = ref(false)
const refRoleForm            = ref<VForm>()
const isSavingRole           = ref(false)
const roleError              = ref('')
const roleForm               = ref({ id: undefined as number | undefined, name: '', permissions: [] as string[] })

function openAddRole() {
  roleForm.value = { id: undefined, name: '', permissions: [] }
  roleError.value = ''
  isAddRoleDialogVisible.value = true
}

function openEditRole(role: any) {
  roleForm.value = { id: role.id, name: role.name, permissions: [...role.permissions] }
  roleError.value = ''
  isRoleDialogVisible.value = true
}

function onRoleReset() {
  isRoleDialogVisible.value    = false
  isAddRoleDialogVisible.value = false
  roleError.value = ''
  nextTick(() => { refRoleForm.value?.reset(); refRoleForm.value?.resetValidation() })
}

// Select all
const checkedCount    = computed(() => roleForm.value.permissions.length)
const totalPerms      = computed(() => roleStore.allPermissions.length)
const isSelectAll     = computed(() => totalPerms.value > 0 && checkedCount.value === totalPerms.value)
const isIndeterminate = computed(() => checkedCount.value > 0 && checkedCount.value < totalPerms.value)

watch(isSelectAll, val => {
  if (!isIndeterminate.value)
    roleForm.value.permissions = val ? [...roleStore.allPermissions] : []
})
watch(isIndeterminate, () => { /* handled by isSelectAll */ })

function toggleAll(val: boolean) {
  roleForm.value.permissions = val ? [...roleStore.allPermissions] : []
}

function isGroupAll(perms: string[])   { return perms.every(p => roleForm.value.permissions.includes(p)) }
function isGroupIndet(perms: string[]) { const c = perms.filter(p => roleForm.value.permissions.includes(p)).length; return c > 0 && c < perms.length }
function toggleGroup(perms: string[], val: boolean) {
  if (val) perms.forEach(p => { if (!roleForm.value.permissions.includes(p)) roleForm.value.permissions.push(p) })
  else roleForm.value.permissions = roleForm.value.permissions.filter(p => !perms.includes(p))
}

async function onRoleSubmit() {
  const { valid } = await refRoleForm.value!.validate()
  if (!valid) return
  isSavingRole.value = true
  roleError.value = ''
  try {
    await roleStore.manageRole(roleForm.value)
    onRoleReset()
    await Promise.all([roleStore.getRoleData(), roleStore.roleStatistic()])
  }
  catch (e: any) {
    roleError.value = e?.data?.message ?? e?.data?.errors?.name?.[0] ?? 'Something went wrong.'
  }
  finally { isSavingRole.value = false }
}

async function deleteRole(id: number) {
  const { isConfirmed } = await Swal.fire({
    title: 'Delete Role?', text: 'This action cannot be undone.', icon: 'warning',
    showCancelButton: true, confirmButtonText: 'Yes, delete it!',
    customClass: { confirmButton: 'v-btn v-btn--elevated bg-error', cancelButton: 'v-btn v-btn--elevated bg-secondary ms-2' },
    buttonsStyling: false,
  })
  if (!isConfirmed) return
  try {
    await roleStore.roleDelete({ id })
    await Promise.all([roleStore.getRoleData(), roleStore.roleStatistic()])
  }
  catch (e: any) {
    Swal.fire({ title: 'Error', text: e?.data?.message ?? 'Cannot delete role.', icon: 'error' })
  }
}

// ── Permissions Table ──────────────────────────────────────────────────────
const permSearch  = ref('')
const permPage    = ref(1)
const permPerPage = ref(10)
const permSortBy  = ref('name')
const permOrderBy = ref('asc')

const permHeaders = [
  { title: 'Name',        key: 'name' },
  { title: 'Assigned To', key: 'assignedTo', sortable: false },
  { title: 'Created',     key: 'created_at', sortable: false },
  { title: 'Actions',     key: 'actions',    sortable: false },
]

async function fetchPermissions() {
  await permStore.getPermissionData({
    searchQuery: permSearch.value,
    page: permPage.value,
    itemsPerPage: permPerPage.value,
    sortBy: permSortBy.value,
    orderBy: permOrderBy.value,
  })
}

function updatePermOptions(options: any) {
  permPage.value    = options.page
  permSortBy.value  = options.sortBy[0]?.key   ?? 'name'
  permOrderBy.value = options.sortBy[0]?.order  ?? 'asc'
  fetchPermissions()
}

watch(permSearch, () => { permPage.value = 1; fetchPermissions() })

// Permission Dialog (AddEditPermissionDialog pattern)
const isPermDialogVisible = ref(false)
const refPermForm         = ref<VForm>()
const isSavingPerm        = ref(false)
const permError           = ref('')
const permForm            = ref({ id: undefined as number | undefined, name: '' })

function openAddPerm() {
  permForm.value = { id: undefined, name: '' }
  permError.value = ''
  isPermDialogVisible.value = true
}

function openEditPerm(item: any) {
  permForm.value = { id: item.id, name: item.name }
  permError.value = ''
  isPermDialogVisible.value = true
}

function onPermReset() {
  isPermDialogVisible.value = false
  permError.value = ''
  nextTick(() => { refPermForm.value?.reset(); refPermForm.value?.resetValidation() })
}

async function onPermSubmit() {
  const { valid } = await refPermForm.value!.validate()
  if (!valid) return
  isSavingPerm.value = true
  permError.value = ''
  try {
    await permStore.managePermission(permForm.value)
    onPermReset()
    await fetchPermissions()
  }
  catch (e: any) {
    permError.value = e?.data?.message ?? e?.data?.errors?.name?.[0] ?? 'Something went wrong.'
  }
  finally { isSavingPerm.value = false }
}

async function deletePerm(id: number, name: string) {
  const { isConfirmed } = await Swal.fire({
    title: 'Delete Permission?', text: `"${name}" will be removed from all roles.`, icon: 'warning',
    showCancelButton: true, confirmButtonText: 'Yes, delete it!',
    customClass: { confirmButton: 'v-btn v-btn--elevated bg-error', cancelButton: 'v-btn v-btn--elevated bg-secondary ms-2' },
    buttonsStyling: false,
  })
  if (!isConfirmed) return
  await permStore.permissionDelete({ id })
  await fetchPermissions()
}

// ── Init ───────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    roleStore.getRoleData(),
    roleStore.roleStatistic(),
    roleStore.getAllPermissions(),
    fetchPermissions(),
  ])
})
</script>

<template>
  <div>
    <!-- Page Tabs -->
    <VTabs v-model="activeTab" class="mb-6">
      <VTab value="roles-permissions">
        <VIcon icon="ri-shield-keyhole-line" class="me-2" />
        Roles & Permissions
      </VTab>
      <VTab value="configuration">
        <VIcon icon="ri-settings-4-line" class="me-2" />
        Configuration
      </VTab>
    </VTabs>

    <VWindow v-model="activeTab">

      <!-- ══════════════════════════════════════════════════════════════════
           TAB 1 — Roles & Permissions  (exact theme pattern)
      ══════════════════════════════════════════════════════════════════════ -->
      <VWindowItem value="roles-permissions">

        <!-- 👉 Role Cards — exact RoleCards.vue pattern -->
        <VRow>
          <VCol
            v-for="role in roleStore.roles"
            :key="role.id"
            cols="12"
            sm="6"
            lg="4"
          >
            <VCard>
              <VCardText class="d-flex align-center pb-4">
                <span>Total {{ role.users_count }} users</span>

                <VSpacer />

                <!-- Avatar group placeholder (no real avatars yet) -->
                <div class="v-avatar-group">
                  <VAvatar
                    v-for="n in Math.min(role.users_count, 3)"
                    :key="n"
                    size="40"
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon icon="ri-user-line" size="20" />
                  </VAvatar>
                  <VAvatar
                    v-if="role.users_count > 4"
                    :color="$vuetify.theme.current.dark ? '#383B55' : '#F0EFF0'"
                  >
                    <span class="text-high-emphasis">+{{ role.users_count - 3 }}</span>
                  </VAvatar>
                </div>
              </VCardText>

              <VCardText>
                <div class="d-flex justify-space-between align-end">
                  <div>
                    <h5 class="text-h5 mb-1 text-capitalize">
                      {{ role.name }}
                    </h5>
                    <a
                      href="javascript:void(0)"
                      @click="openEditRole(role)"
                    >
                      Edit Role
                    </a>
                  </div>

                  <IconBtn
                    color="secondary"
                    class="mt-n2"
                    :disabled="role.users_count > 0"
                    @click="deleteRole(role.id)"
                  >
                    <VIcon icon="ri-delete-bin-7-line" />
                    <VTooltip activator="parent">
                      {{ role.users_count > 0 ? 'Has assigned users' : 'Delete' }}
                    </VTooltip>
                  </IconBtn>
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <!-- 👉 Add New Role card — exact theme pattern -->
          <VCol cols="12" sm="6" lg="4">
            <VCard class="h-100" :ripple="false">
              <VRow no-gutters class="h-100">
                <VCol cols="5" class="d-flex flex-column justify-end align-center">
                  <img width="69" :src="poseM">
                </VCol>
                <VCol cols="7">
                  <VCardText class="d-flex flex-column align-end justify-end gap-4">
                    <VBtn size="small" @click="openAddRole">
                      Add Role
                    </VBtn>
                    <span class="text-end">Add new role, if it doesn't exist.</span>
                  </VCardText>
                </VCol>
              </VRow>
            </VCard>
          </VCol>
        </VRow>

        <!-- 👉 Permissions Table — exact theme permissions page pattern -->
        <VCard class="mt-6">
          <VCardText class="d-flex justify-space-between flex-wrap gap-5">
            <VBtn
              variant="outlined"
              color="secondary"
              prepend-icon="ri-share-box-line"
            >
              Export
            </VBtn>

            <div class="d-flex flex-wrap gap-4">
              <div style="inline-size: 15.625rem;">
                <VTextField
                  v-model="permSearch"
                  placeholder="Search Permission"
                  density="compact"
                />
              </div>
              <VBtn prepend-icon="ri-add-line" @click="openAddPerm">
                Add Permission
              </VBtn>
            </div>
          </VCardText>

          <VDivider />

          <VDataTableServer
            v-model:items-per-page="permPerPage"
            :items="permStore.permissions"
            :items-length="permStore.totalPerms"
            :headers="permHeaders"
            :loading="permStore.loading"
            item-value="id"
            class="text-no-wrap rounded-0"
            :items-per-page-options="[
              { value: 10, title: '10' },
              { value: 20, title: '20' },
              { value: 50, title: '50' },
              { value: -1, title: '$vuetify.dataFooter.itemsPerPageAll' },
            ]"
            @update:options="updatePermOptions"
          >
            <!-- Name -->
            <template #item.name="{ item }">
              <h6 class="text-h6">{{ permLabel(item.name) }}</h6>
              <span class="text-caption text-medium-emphasis">{{ item.name }}</span>
            </template>

            <!-- Assigned To -->
            <template #item.assignedTo="{ item }">
              <div class="d-flex flex-wrap gap-1">
                <VChip
                  v-for="role in item.roles"
                  :key="role"
                  size="small"
                  class="text-capitalize"
                >
                  {{ role }}
                </VChip>
                <span v-if="!item.roles.length" class="text-caption text-disabled">—</span>
              </div>
            </template>

            <!-- Created -->
            <template #item.created_at="{ item }">
              <span class="text-body-2">{{ item.created_at ?? '—' }}</span>
            </template>

            <!-- Actions -->
            <template #item.actions="{ item }">
              <IconBtn size="small" @click="openEditPerm(item)">
                <VIcon icon="ri-edit-box-line" />
                <VTooltip activator="parent">Edit</VTooltip>
              </IconBtn>
              <IconBtn size="small" color="error" @click="deletePerm(item.id, item.name)">
                <VIcon icon="ri-delete-bin-7-line" />
                <VTooltip activator="parent">Delete</VTooltip>
              </IconBtn>
            </template>

            <!-- Pagination -->
            <template #bottom>
              <VDivider />
              <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
                <div class="d-flex align-center gap-x-2 text-medium-emphasis text-base">
                  Rows Per Page:
                  <VSelect
                    v-model="permPerPage"
                    class="per-page-select"
                    variant="plain"
                    :items="[10, 20, 25, 50, 100]"
                  />
                </div>
                <p class="d-flex align-center text-base text-high-emphasis me-2 mb-0">
                  {{ paginationMeta({ page: permPage, itemsPerPage: permPerPage }, permStore.totalPerms) }}
                </p>
                <div class="d-flex gap-x-2 align-center me-2">
                  <VBtn
                    class="flip-in-rtl"
                    icon="ri-arrow-left-s-line"
                    variant="text"
                    density="comfortable"
                    color="high-emphasis"
                    :disabled="permPage <= 1"
                    @click="permPage <= 1 ? permPage = 1 : permPage--; fetchPermissions()"
                  />
                  <VBtn
                    class="flip-in-rtl"
                    icon="ri-arrow-right-s-line"
                    variant="text"
                    density="comfortable"
                    color="high-emphasis"
                    :disabled="permPage >= Math.ceil(permStore.totalPerms / permPerPage)"
                    @click="permPage >= Math.ceil(permStore.totalPerms / permPerPage) ? null : permPage++; fetchPermissions()"
                  />
                </div>
              </div>
            </template>
          </VDataTableServer>
        </VCard>

      </VWindowItem>

      <!-- ══════════════════════════════════════════════════════════════════
           TAB 2 — Configuration
      ══════════════════════════════════════════════════════════════════════ -->
      <VWindowItem value="configuration">
        <VRow>
          <VCol cols="12" md="6">
            <VCard title="General Settings">
              <VCardText>
                <VRow>
                  <VCol cols="12">
                    <VTextField label="App Name" placeholder="AI News Filter" persistent-placeholder />
                  </VCol>
                  <VCol cols="12">
                    <VTextField label="Support Email" placeholder="support@example.com" persistent-placeholder />
                  </VCol>
                  <VCol cols="12">
                    <VSelect
                      label="Default Role for New Users"
                      :items="roleStore.roles.map(r => ({ title: r.name, value: r.name }))"
                      placeholder="Select role"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VBtn>Save Changes</VBtn>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" md="6">
            <VCard title="Access Control">
              <VCardText>
                <VRow>
                  <VCol cols="12">
                    <VSwitch label="Allow User Registration" color="primary" :model-value="true" />
                  </VCol>
                  <VCol cols="12">
                    <VSwitch label="Require Email Verification" color="primary" :model-value="false" />
                  </VCol>
                  <VCol cols="12">
                    <VSwitch label="Enable Two-Factor Authentication" color="primary" :model-value="false" />
                  </VCol>
                  <VCol cols="12">
                    <VTextField label="Session Timeout (minutes)" placeholder="60" type="number" persistent-placeholder />
                  </VCol>
                  <VCol cols="12">
                    <VBtn>Save Changes</VBtn>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>

    </VWindow>

    <!-- ══════════════════════════════════════════════════════════════════
         Add / Edit Role Dialog — exact AddEditRoleDialog.vue pattern
    ══════════════════════════════════════════════════════════════════════ -->
    <VDialog
      :width="$vuetify.display.smAndDown ? 'auto' : 900"
      :model-value="isRoleDialogVisible || isAddRoleDialogVisible"
      @update:model-value="onRoleReset"
    >
      <VCard class="pa-sm-11 pa-3">
        <DialogCloseBtn variant="text" size="default" @click="onRoleReset" />

        <VCardText>
          <div class="text-center mb-10">
            <h4 class="text-h4 mb-2">
              {{ roleForm.id ? 'Edit' : 'Add' }} Role
            </h4>
            <p class="text-body-1">
              {{ roleForm.id ? 'Edit' : 'Add' }} Role
            </p>
          </div>

          <VForm ref="refRoleForm" @submit.prevent="onRoleSubmit">
            <VTextField
              v-model="roleForm.name"
              label="Role Name"
              placeholder="Enter Role Name"
              :rules="[requiredValidator]"
            />

            <VAlert v-if="roleError" type="error" variant="tonal" class="mt-4" closable @click:close="roleError = ''">
              {{ roleError }}
            </VAlert>

            <h5 class="text-h5 my-6">Role Permissions</h5>

            <VTable class="permission-table text-no-wrap">
              <!-- Administrator Access / Select All -->
              <tr>
                <td class="text-h6">Administrator Access</td>
                <td colspan="3">
                  <div class="d-flex justify-end">
                    <VCheckbox
                      :model-value="isSelectAll"
                      :indeterminate="isIndeterminate"
                      label="Select All"
                      @update:model-value="toggleAll"
                    />
                  </div>
                </td>
              </tr>

              <!-- Permission Groups -->
              <template v-for="group in GROUPS" :key="group.label">
                <!-- Group header row -->
                <tr>
                  <td
                    colspan="4"
                    class="text-subtitle-2 font-weight-bold"
                    style="background: rgba(var(--v-theme-on-surface), 0.04);"
                  >
                    <div class="d-flex align-center justify-space-between">
                      <span>{{ group.label }}</span>
                      <VCheckbox
                        :model-value="isGroupAll(group.perms)"
                        :indeterminate="isGroupIndet(group.perms)"
                        label="All"
                        density="compact"
                        hide-details
                        @update:model-value="val => toggleGroup(group.perms, !!val)"
                      />
                    </div>
                  </td>
                </tr>
                <!-- Individual permission rows -->
                <tr v-for="perm in group.perms" :key="perm">
                  <td class="text-h6">{{ permLabel(perm) }}</td>
                  <td colspan="3">
                    <div class="d-flex justify-end">
                      <VCheckbox
                        v-model="roleForm.permissions"
                        :value="perm"
                        hide-details
                      />
                    </div>
                  </td>
                </tr>
              </template>
            </VTable>

            <div class="d-flex align-center justify-center gap-3 mt-6">
              <VBtn type="submit" :loading="isSavingRole">Submit</VBtn>
              <VBtn color="secondary" variant="outlined" @click="onRoleReset">Cancel</VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- ══════════════════════════════════════════════════════════════════
         Add / Edit Permission Dialog — exact AddEditPermissionDialog.vue pattern
    ══════════════════════════════════════════════════════════════════════ -->
    <VDialog
      :width="$vuetify.display.smAndDown ? 'auto' : 600"
      :model-value="isPermDialogVisible"
      @update:model-value="onPermReset"
    >
      <VCard class="pa-sm-8 pa-5">
        <DialogCloseBtn variant="text" size="default" @click="onPermReset" />

        <VCardText class="mt-5">
          <div class="text-center mb-6">
            <h4 class="text-h4 mb-2">
              {{ permForm.id ? 'Edit' : 'Add' }} Permission
            </h4>
            <p class="text-body-1">
              {{ permForm.id ? 'Edit' : 'Add' }} permission as per your requirements.
            </p>
          </div>

          <VForm ref="refPermForm" @submit.prevent="onPermSubmit">
            <VAlert type="warning" title="Warning!" variant="tonal" class="mb-6">
              By {{ permForm.id ? 'editing' : 'adding' }} the permission name, you might break the system permissions functionality. Please ensure you're absolutely certain before proceeding.
            </VAlert>

            <VAlert v-if="permError" type="error" variant="tonal" class="mb-4" closable @click:close="permError = ''">
              {{ permError }}
            </VAlert>

            <div class="d-flex align-center gap-4 mb-4">
              <VTextField
                v-model="permForm.name"
                density="compact"
                placeholder="Enter Permission Name"
                :rules="[requiredValidator]"
              />
              <VBtn type="submit" :loading="isSavingPerm">
                {{ permForm.id ? 'Update' : 'Add' }}
              </VBtn>
            </div>

            <VCheckbox label="Set as core permission" />
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style lang="scss">
.permission-table {
  td {
    border-block-end: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    padding-block: 0.625rem;

    .v-checkbox { min-inline-size: 4.75rem; }

    &:not(:first-child) { padding-inline: 0.75rem; }

    .v-label { white-space: nowrap; }
  }
}
</style>
