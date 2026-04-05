<script setup lang="ts">
import { $api } from '@/utils/api'

definePage({ meta: { action: 'read', subject: 'Dashboard' } })

const stats          = ref<any>(null)
const recentArticles = ref<any[]>([])
const digestHistory  = ref<any[]>([])
const loading        = ref(true)

const statCards = computed(() => {
  if (!stats.value) return []
  return [
    {
      title: 'Total Articles',
      value: stats.value.articles.total,
      icon: 'ri-newspaper-line',
      color: 'primary',
      subtitle: `${stats.value.articles.today} fetched today`,
    },
    {
      title: 'My Categories',
      value: stats.value.user.my_categories,
      icon: 'ri-price-tag-3-line',
      color: 'success',
      subtitle: `${stats.value.categories.active} active categories`,
    },
    {
      title: 'AI Summaries',
      value: stats.value.articles.summarized,
      icon: 'ri-sparkling-line',
      color: 'warning',
      subtitle: 'Articles summarized by AI',
    },
    {
      title: 'Digests Sent',
      value: stats.value.digest.my_sent,
      icon: 'ri-mail-send-line',
      color: 'info',
      subtitle: `${stats.value.digest.sent} total sent`,
    },
  ]
})

const digestStatusColor: Record<string, string> = {
  sent: 'success', failed: 'error', skipped: 'warning',
}

onMounted(async () => {
  try {
    const [statsRes, articlesRes, digestRes] = await Promise.all([
      $api('getDashboardStatistic'),
      $api('getRecentArticles'),
      $api('getDigestHistory'),
    ])
    stats.value          = statsRes.data
    recentArticles.value = articlesRes.data
    digestHistory.value  = digestRes.data
  }
  finally { loading.value = false }
})
</script>

<template>
  <div>
    <!-- Stat Cards -->
    <VRow class="mb-6">
      <!-- Loading skeletons -->
      <template v-if="loading">
        <VCol v-for="n in 4" :key="n" cols="12" sm="6" lg="3">
          <VCard>
            <VCardText class="d-flex align-center gap-4">
              <VSkeletonLoader type="avatar" width="42" height="42" />
              <div class="flex-grow-1">
                <VSkeletonLoader type="text" class="mb-1" />
                <VSkeletonLoader type="text" width="60%" />
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </template>

      <!-- Loaded stat cards -->
      <template v-else>
        <VCol v-for="card in statCards" :key="card.title" cols="12" sm="6" lg="3">
          <VCard>
            <VCardText class="d-flex align-center gap-4">
              <VAvatar :color="card.color" variant="tonal" rounded size="42">
                <VIcon :icon="card.icon" size="26" />
              </VAvatar>
              <div>
                <p class="text-body-2 text-medium-emphasis mb-0">{{ card.title }}</p>
                <h5 class="text-h5">{{ card.value }}</h5>
                <p class="text-caption text-medium-emphasis mb-0">{{ card.subtitle }}</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </template>
    </VRow>

    <VRow>
      <!-- Recent Articles -->
      <VCol cols="12" md="8">
        <VCard title="Recent Articles For You">
          <template #append>
            <RouterLink :to="{ name: 'articles-listing' }" class="text-primary text-body-2">
              View All
            </RouterLink>
          </template>

          <VDivider />

          <VCardText v-if="loading" class="pa-4">
            <VSkeletonLoader v-for="n in 3" :key="n" type="list-item-avatar-two-line" class="mb-2" />
          </VCardText>

          <VList v-else-if="recentArticles.length" lines="two">
            <VListItem
              v-for="article in recentArticles"
              :key="article.id"
              :href="article.url"
              target="_blank"
              class="px-4"
            >
              <template #prepend>
                <VAvatar rounded size="44" :image="article.image_url || undefined" color="primary" variant="tonal">
                  <VIcon v-if="!article.image_url" icon="ri-newspaper-line" />
                </VAvatar>
              </template>

              <VListItemTitle class="font-weight-medium text-wrap">
                {{ article.title }}
              </VListItemTitle>
              <VListItemSubtitle>
                <span v-if="article.summary" class="text-wrap">{{ article.summary.slice(0, 100) }}...</span>
                <span v-else class="text-disabled">No summary yet</span>
              </VListItemSubtitle>

              <template #append>
                <div class="d-flex flex-column align-end gap-1">
                  <VChip size="x-small" color="primary" variant="tonal">{{ article.category }}</VChip>
                  <span class="text-caption text-disabled">{{ article.published_at }}</span>
                </div>
              </template>
            </VListItem>
          </VList>

          <VCardText v-else class="text-center py-8 text-medium-emphasis">
            <VIcon icon="ri-newspaper-line" size="48" class="mb-3 d-block" />
            <p class="mb-1">No articles yet</p>
            <p class="text-caption">Select categories in Preferences to start receiving articles</p>
            <VBtn size="small" :to="{ name: 'preferences' }" class="mt-2">Set Preferences</VBtn>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Digest History -->
      <VCol cols="12" md="4">
        <VCard title="Digest History" class="h-100">
          <template #append>
            <RouterLink :to="{ name: 'digest-listing' }" class="text-primary text-body-2">
              View All
            </RouterLink>
          </template>

          <VDivider />

          <VCardText v-if="loading">
            <VSkeletonLoader v-for="n in 5" :key="n" type="list-item-two-line" class="mb-1" />
          </VCardText>

          <VList v-else-if="digestHistory.length" lines="two">
            <VListItem v-for="log in digestHistory" :key="log.id" class="px-4">
              <template #prepend>
                <VAvatar :color="digestStatusColor[log.status]" variant="tonal" size="36" rounded>
                  <VIcon
                    :icon="log.status === 'sent' ? 'ri-mail-check-line' : log.status === 'failed' ? 'ri-mail-close-line' : 'ri-mail-line'"
                    size="18"
                  />
                </VAvatar>
              </template>
              <VListItemTitle class="text-capitalize">{{ log.status }}</VListItemTitle>
              <VListItemSubtitle>{{ log.article_count }} articles · {{ log.sent_at }}</VListItemSubtitle>
            </VListItem>
          </VList>

          <VCardText v-else class="text-center py-8 text-medium-emphasis">
            <VIcon icon="ri-mail-send-line" size="48" class="mb-3 d-block" />
            <p class="mb-0">No digests sent yet</p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
