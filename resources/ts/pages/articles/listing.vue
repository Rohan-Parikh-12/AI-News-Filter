<script setup lang="ts">
import ArticleListingTable from '@/views/articles/ArticleListingTable.vue'
import { useArticleStore } from '@/stores/article'

definePage({ meta: { action: 'read', subject: 'Articles' } })

const store = useArticleStore()

const stats = computed(() => [
  { title: 'Total Articles',  value: store.statistic.total,      icon: 'ri-newspaper-line',       color: 'primary' },
  { title: 'Fetched Today',   value: store.statistic.today,      icon: 'ri-calendar-check-line',  color: 'success' },
  { title: 'AI Summarized',   value: store.statistic.summarized, icon: 'ri-sparkling-line',        color: 'warning' },
  { title: 'Saved by Me',     value: store.statistic.saved,      icon: 'ri-bookmark-line',         color: 'info'    },
])

onMounted(() => store.articleStatistic())
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

    <ArticleListingTable />
  </div>
</template>
