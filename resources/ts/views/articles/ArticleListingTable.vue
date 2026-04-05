<script setup lang="ts">
import { useArticleStore } from '@/stores/article'
import { useCategoryStore } from '@/stores/category'
import { paginationMeta } from '@/utils/paginationMeta'

const store    = useArticleStore()
const catStore = useCategoryStore()

const searchQuery  = ref('')
const itemsPerPage = ref(10)
const page         = ref(1)
const sortBy       = ref('published_at')
const orderBy      = ref('desc')
const filterCat    = ref<number | null>(null)
const showSaved    = ref(false)
const initialized  = ref(false)

const headers = [
  { title: 'Article',   key: 'title' },
  { title: 'Category',  key: 'category' },
  { title: 'Summary',   key: 'summary',     sortable: false },
  { title: 'Published', key: 'published_at' },
  { title: 'Actions',   key: 'actions',     sortable: false },
]

async function fetchData() {
  await store.getArticleData({
    searchQuery:  searchQuery.value,
    page:         page.value,
    itemsPerPage: itemsPerPage.value,
    sortBy:       sortBy.value,
    orderBy:      orderBy.value,
    category_id:  filterCat.value || undefined,
    saved:        showSaved.value ? 1 : undefined,
  })
}

// VDataTableServer fires update:options on mount — use it as the single fetch trigger
function updateOptions(options: any) {
  page.value    = options.page
  sortBy.value  = options.sortBy[0]?.key   ?? 'published_at'
  orderBy.value = options.sortBy[0]?.order ?? 'desc'
  if (initialized.value) fetchData()
}

watch([searchQuery, filterCat, showSaved], () => {
  page.value = 1
  fetchData()
})

async function toggleSave(article: any) {
  if (article.is_saved)
    await store.articleUnsave(article.id)
  else
    await store.articleSave(article.id)

  article.is_saved = !article.is_saved
  store.articleStatistic()
}

const categoryOptions = computed(() => [
  { title: 'All Categories', value: null },
  ...catStore.categories.map(c => ({ title: c.name, value: c.id })),
])

onMounted(async () => {
  await catStore.getCategoryData({ itemsPerPage: 100 })
  initialized.value = true
  await fetchData()
})
</script>

<template>
  <div>
    <VCard>
      <VCardText class="d-flex justify-space-between flex-wrap gap-4">
        <div class="d-flex flex-wrap gap-4">
          <div style="inline-size: 15.625rem;">
            <VTextField
              v-model="searchQuery"
              placeholder="Search Articles"
              density="compact"
            />
          </div>
          <div style="inline-size: 12rem;">
            <VSelect
              v-model="filterCat"
              :items="categoryOptions"
              density="compact"
              placeholder="All Categories"
              clearable
              clear-icon="ri-close-line"
            />
          </div>
        </div>

        <VBtn
          :variant="showSaved ? 'elevated' : 'outlined'"
          :color="showSaved ? 'primary' : 'secondary'"
          prepend-icon="ri-bookmark-line"
          @click="showSaved = !showSaved"
        >
          Saved Only
        </VBtn>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="store.articles"
        :items-length="store.total"
        :headers="headers"
        :loading="store.loading"
        item-value="id"
        class="text-no-wrap rounded-0"
        :items-per-page-options="[
          { value: 10, title: '10' },
          { value: 20, title: '20' },
          { value: 50, title: '50' },
        ]"
        @update:options="updateOptions"
      >
        <!-- Title -->
        <template #item.title="{ item }">
          <div class="d-flex align-center gap-3 py-2" style="max-inline-size: 340px;">
            <VAvatar rounded size="40" :image="item.image_url || undefined" color="primary" variant="tonal">
              <VIcon v-if="!item.image_url" icon="ri-newspaper-line" size="20" />
            </VAvatar>
            <div>
              <a
                :href="item.url"
                target="_blank"
                class="text-body-2 font-weight-medium text-high-emphasis d-block"
                style="max-inline-size: 280px; white-space: normal; line-height: 1.4;"
              >
                {{ item.title }}
              </a>
              <span class="text-caption text-medium-emphasis">{{ item.source_name }}</span>
            </div>
          </div>
        </template>

        <!-- Category -->
        <template #item.category="{ item }">
          <VChip size="small" color="primary" variant="tonal">{{ item.category }}</VChip>
        </template>

        <!-- Summary -->
        <template #item.summary="{ item }">
          <div style="max-inline-size: 280px; white-space: normal;">
            <span v-if="item.summary" class="text-body-2">
              {{ item.summary.slice(0, 100) }}{{ item.summary.length > 100 ? '...' : '' }}
            </span>
            <VChip v-else size="x-small" color="warning" variant="tonal">Pending AI</VChip>
          </div>
        </template>

        <!-- Published -->
        <template #item.published_at="{ item }">
          <span class="text-body-2 text-no-wrap">{{ item.published_at }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn size="small" :color="item.is_saved ? 'primary' : 'default'" @click="toggleSave(item)">
            <VIcon :icon="item.is_saved ? 'ri-bookmark-fill' : 'ri-bookmark-line'" />
            <VTooltip activator="parent">{{ item.is_saved ? 'Unsave' : 'Save' }}</VTooltip>
          </IconBtn>
          <IconBtn size="small" :href="item.url" target="_blank">
            <VIcon icon="ri-external-link-line" />
            <VTooltip activator="parent">Open Article</VTooltip>
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
                @click="page--; fetchData()"
              />
              <VBtn
                class="flip-in-rtl" icon="ri-arrow-right-s-line" variant="text"
                density="comfortable" color="high-emphasis"
                :disabled="page >= Math.ceil(store.total / itemsPerPage)"
                @click="page++; fetchData()"
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
    </VCard>
  </div>
</template>
