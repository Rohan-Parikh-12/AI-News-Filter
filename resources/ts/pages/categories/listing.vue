<script setup lang="ts">
import CategoryListingTable from '@/views/categories/CategoryListingTable.vue'
import { useCategoryStore } from '@/stores/category'

definePage({ meta: { action: 'read', subject: 'Categories' } })

const store = useCategoryStore()

const stats = computed(() => [
  { title: 'Total Categories', value: store.statistic.total,    icon: 'ri-price-tag-3-line',    color: 'primary' },
  { title: 'Active',           value: store.statistic.active,   icon: 'ri-checkbox-circle-line', color: 'success' },
  { title: 'Inactive',         value: store.statistic.inactive, icon: 'ri-close-circle-line',    color: 'error'   },
  { title: 'Archived',         value: store.statistic.archive,  icon: 'ri-archive-line',         color: 'warning' },
])

onMounted(() => store.categoryStatistic())
</script>

<template>
  <div>
    <VRow class="mb-6">
      <VCol v-for="stat in stats" :key="stat.title" cols="12" sm="6" lg="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar :color="stat.color" variant="tonal" rounded size="42">
              <VIcon :icon="stat.icon" size="26" />
            </VAvatar>
            <div>
              <p class="text-body-2 text-medium-emphasis mb-0">{{ stat.title }}</p>
              <h5 class="text-h5">{{ stat.value }}</h5>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <CategoryListingTable />
  </div>
</template>
