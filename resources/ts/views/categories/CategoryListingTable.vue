<script setup lang="ts">
import { useCategoryStore } from '@/stores/category'
import { paginationMeta } from '@/utils/paginationMeta'
import { useAuthStore } from '@/stores/auth'
import CategoryDrawer from '@/components/drawers/CategoryDrawer.vue'

const store     = useCategoryStore()
const authStore = useAuthStore()

const searchQuery  = ref('')
const itemsPerPage = ref(10)
const page         = ref(1)
const sortBy       = ref('sort_order')
const orderBy      = ref('asc')
const selectedRows = ref<number[]>([])
const drawerRef    = ref<InstanceType<typeof CategoryDrawer>>()
const initialized  = ref(false)

const headers = [
  { title: 'Name',        key: 'name' },
  { title: 'Keyword',     key: 'api_keyword' },
  { title: 'Articles',    key: 'articles_count' },
  { title: 'Sort',        key: 'sort_order' },
  { title: 'Status',      key: 'status' },
  { title: 'Actions',     key: 'actions', sortable: false },
]

const statusMap: Record<string, { color: string; label: string }> = {
  '1': { color: 'success', label: 'Active' },
  '2': { color: 'error',   label: 'Inactive' },
  '0': { color: 'warning', label: 'Archive' },
}

async function fetchData() {
  await store.getCategoryData({
    searchQuery: searchQuery.value,
    page: page.value,
    itemsPerPage: itemsPerPage.value,
    sortBy: sortBy.value,
    orderBy: orderBy.value,
  })
}

function updateOptions(options: any) {
  page.value    = options.page
  sortBy.value  = options.sortBy[0]?.key   ?? 'sort_order'
  orderBy.value = options.sortBy[0]?.order  ?? 'asc'
  if (initialized.value) fetchData()
}

watch(searchQuery, () => { page.value = 1; fetchData() })

function openAdd() {
  drawerRef.value?.open()
  isDrawerOpen.value = true
}

function openEdit(item: any) {
  drawerRef.value?.open(item)
  isDrawerOpen.value = true
}

async function onStatusChange(id: number, newStatus: '0' | '1' | '2') {
  await store.categoryStatusChange(id, newStatus)
  await fetchData()
  await store.categoryStatistic()
}

async function onDelete(id: number) {
  const { isConfirmed } = await Swal.fire({
    title: 'Delete Category?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    customClass: { confirmButton: 'v-btn v-btn--elevated bg-error', cancelButton: 'v-btn v-btn--elevated bg-secondary ms-2' },
    buttonsStyling: false,
  })
  if (!isConfirmed) return
  try {
    await store.categoryDelete({ id })
    await fetchData()
    await store.categoryStatistic()
  }
  catch (e: any) {
    Swal.fire({ title: 'Error', text: e?.data?.message ?? 'Cannot delete category.', icon: 'error' })
  }
}

onMounted(async () => {
  initialized.value = true
  await fetchData()
})
</script>

<template>
  <div>
    <VCard>
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
              v-model="searchQuery"
              placeholder="Search Category"
              density="compact"
            />
          </div>

          <VBtn
            v-if="authStore.has_permission('categories-create')"
            prepend-icon="ri-add-line"
            @click="openAdd"
          >
            Add Category
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:model-value="selectedRows"
        v-model:items-per-page="itemsPerPage"
        :items="store.categories"
        :items-length="store.total"
        :headers="headers"
        :loading="store.loading"
        item-value="id"
        show-select
        class="text-no-wrap rounded-0"
        :items-per-page-options="[
          { value: 10, title: '10' },
          { value: 20, title: '20' },
          { value: 50, title: '50' },
          { value: -1, title: '$vuetify.dataFooter.itemsPerPageAll' },
        ]"
        @update:options="updateOptions"
      >
        <!-- Name -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-2">
            <VIcon v-if="item.icon" :icon="item.icon" size="20" color="primary" />
            <div>
              <h6 class="text-h6">{{ item.name }}</h6>
              <span class="text-caption text-medium-emphasis">{{ item.slug }}</span>
            </div>
          </div>
        </template>

        <!-- Articles count -->
        <template #item.articles_count="{ item }">
          <VChip size="small" color="info" variant="tonal">{{ item.articles_count }}</VChip>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="statusMap[item.status]?.color"
            size="small"
            class="text-capitalize"
          >
            {{ statusMap[item.status]?.label }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <!-- Edit -->
          <IconBtn
            v-if="authStore.has_permission('categories-edit')"
            size="small"
            @click="openEdit(item)"
          >
            <VIcon icon="ri-edit-box-line" />
            <VTooltip activator="parent">Edit</VTooltip>
          </IconBtn>

          <!-- Status change -->
          <IconBtn
            v-if="authStore.has_permission('categories-status')"
            size="small"
          >
            <VIcon icon="ri-more-2-line" />
            <VMenu activator="parent">
              <VList>
                <VListItem v-if="item.status !== '1'" @click="onStatusChange(item.id, '1')">
                  <template #prepend><VIcon size="20" icon="ri-checkbox-circle-line" /></template>
                  <VListItemTitle>Set Active</VListItemTitle>
                </VListItem>
                <VListItem v-if="item.status !== '2'" @click="onStatusChange(item.id, '2')">
                  <template #prepend><VIcon size="20" icon="ri-close-circle-line" /></template>
                  <VListItemTitle>Set Inactive</VListItemTitle>
                </VListItem>
                <VListItem v-if="item.status !== '0'" @click="onStatusChange(item.id, '0')">
                  <template #prepend><VIcon size="20" icon="ri-archive-line" /></template>
                  <VListItemTitle>Archive</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </IconBtn>

          <!-- Delete — lock icon if has_related_data -->
          <IconBtn
            v-if="authStore.has_permission('categories-delete')"
            size="small"
            :color="item.has_related_data ? 'secondary' : 'error'"
            :disabled="item.has_related_data"
            @click="!item.has_related_data && onDelete(item.id)"
          >
            <VIcon :icon="item.has_related_data ? 'ri-lock-line' : 'ri-delete-bin-7-line'" />
            <VTooltip activator="parent">
              {{ item.has_related_data ? 'Has related articles' : 'Delete' }}
            </VTooltip>
          </IconBtn>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <VDivider />
          <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
            <div class="d-flex align-center gap-x-2 text-medium-emphasis text-base">
              Rows Per Page:
              <VSelect v-model="itemsPerPage" class="per-page-select" variant="plain" :items="[10, 20, 25, 50, 100]" />
            </div>
            <p class="d-flex align-center text-base text-high-emphasis me-2 mb-0">
              {{ paginationMeta({ page, itemsPerPage }, store.total) }}
            </p>
            <div class="d-flex gap-x-2 align-center me-2">
              <VBtn
                class="flip-in-rtl" icon="ri-arrow-left-s-line" variant="text"
                density="comfortable" color="high-emphasis"
                :disabled="page <= 1"
                @click="page <= 1 ? page = 1 : page--; fetchData()"
              />
              <VBtn
                class="flip-in-rtl" icon="ri-arrow-right-s-line" variant="text"
                density="comfortable" color="high-emphasis"
                :disabled="page >= Math.ceil(store.total / itemsPerPage)"
                @click="page >= Math.ceil(store.total / itemsPerPage) ? null : page++; fetchData()"
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
    </VCard>

    <CategoryDrawer
      ref="drawerRef"
      v-model:is-drawer-open="isDrawerOpen"
      @saved="fetchData(); store.categoryStatistic()"
    />
  </div>
</template>
