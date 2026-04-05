<script setup lang="ts">
import { $api } from '@/utils/api'

definePage({ meta: { action: 'read', subject: 'Digest' } })

const loading        = ref(true)
const grouped        = ref<any[]>([])
const total          = ref(0)
const digestSetting  = ref<any>(null)
const message        = ref('')

// History
const historyLoading = ref(false)
const historyLogs    = ref<any[]>([])
const historyTotal   = ref(0)
const historyPage    = ref(1)
const historyPerPage = ref(10)

// Stats
const stats = ref({ total_sent: 0, total_failed: 0, my_categories: 0, available: 0 })

const statCards = computed(() => [
  { title: 'Digests Sent',    value: stats.value.total_sent,    icon: 'ri-mail-check-line',   color: 'success' },
  { title: 'My Categories',   value: stats.value.my_categories, icon: 'ri-price-tag-3-line',  color: 'primary' },
  { title: 'Available Today', value: stats.value.available,     icon: 'ri-newspaper-line',    color: 'info'    },
  { title: 'Failed',          value: stats.value.total_failed,  icon: 'ri-mail-close-line',   color: 'error'   },
])

async function loadToday() {
  loading.value = true
  try {
    const res = await $api('getDigestToday')
    grouped.value       = res.data.articles
    total.value         = res.data.total
    digestSetting.value = res.data.digest_setting
    message.value       = res.data.message
  }
  finally { loading.value = false }
}

async function loadStats() {
  const res = await $api('getDigestStatistic')
  stats.value = res.data
}

async function loadHistory() {
  historyLoading.value = true
  try {
    const res = await $api('getDigestData', {
      query: { page: historyPage.value, itemsPerPage: historyPerPage.value },
    })
    historyLogs.value  = res.data
    historyTotal.value = res.total
  }
  finally { historyLoading.value = false }
}

async function toggleSave(article: any) {
  if (article.is_saved) {
    await $api('articleUnsave', { method: 'POST', body: { article_id: article.id } })
  }
  else {
    await $api('articleSave', { method: 'POST', body: { article_id: article.id } })
  }
  article.is_saved = !article.is_saved
}

const statusColor: Record<string, string> = { sent: 'success', failed: 'error', skipped: 'warning' }
const statusIcon: Record<string, string>  = { sent: 'ri-mail-check-line', failed: 'ri-mail-close-line', skipped: 'ri-mail-line' }

onMounted(async () => {
  await Promise.all([loadToday(), loadStats(), loadHistory()])
})
</script>

<template>
  <div>
    <!-- Stat Cards -->
    <VRow class="mb-6">
      <VCol v-for="card in statCards" :key="card.title" cols="12" sm="6" lg="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar :color="card.color" variant="tonal" rounded size="42">
              <VIcon :icon="card.icon" size="26" />
            </VAvatar>
            <div>
              <p class="text-body-2 text-medium-emphasis mb-0">{{ card.title }}</p>
              <h5 class="text-h5">{{ card.value }}</h5>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <!-- Today's Articles -->
      <VCol cols="12" md="8">
        <VCard>
          <VCardText class="d-flex align-center justify-space-between pb-0">
            <div>
              <h5 class="text-h5 mb-1">Today's Digest</h5>
              <p class="text-body-2 text-medium-emphasis mb-0">
                {{ total }} article{{ total !== 1 ? 's' : '' }} from your selected categories
              </p>
            </div>
            <VChip
              v-if="digestSetting"
              size="small"
              :color="digestSetting.digest_enabled ? 'success' : 'default'"
              variant="tonal"
              prepend-icon="ri-time-line"
            >
              {{ digestSetting.digest_enabled ? digestSetting.send_time : 'Disabled' }}
            </VChip>
          </VCardText>

          <VDivider class="mt-4" />

          <!-- Loading -->
          <VCardText v-if="loading">
            <VSkeletonLoader v-for="n in 3" :key="n" type="article" class="mb-4" />
          </VCardText>

          <!-- No categories selected -->
          <VCardText v-else-if="message === 'no_categories'" class="text-center py-12">
            <VIcon icon="ri-price-tag-3-line" size="64" color="primary" class="mb-4 d-block" />
            <h5 class="text-h5 mb-2">No categories selected</h5>
            <p class="text-body-1 text-medium-emphasis mb-4">
              Select your news interests to start receiving your personalised digest.
            </p>
            <VBtn :to="{ name: 'preferences' }" prepend-icon="ri-settings-3-line">
              Set Preferences
            </VBtn>
          </VCardText>

          <!-- No articles yet -->
          <VCardText v-else-if="message === 'no_articles'" class="text-center py-12">
            <VIcon icon="ri-newspaper-line" size="64" color="warning" class="mb-4 d-block" />
            <h5 class="text-h5 mb-2">No articles yet</h5>
            <p class="text-body-1 text-medium-emphasis mb-4">
              Articles are fetched every 3 hours. Check back soon!
            </p>
            <VBtn variant="outlined" prepend-icon="ri-refresh-line" @click="loadToday">
              Refresh
            </VBtn>
          </VCardText>

          <!-- Grouped articles -->
          <template v-else>
            <div v-for="group in grouped" :key="group.category" class="mb-2">
              <!-- Category header -->
              <VCardText class="pb-0 pt-4">
                <div class="d-flex align-center gap-2">
                  <VIcon icon="ri-price-tag-3-line" size="16" color="primary" />
                  <span class="text-subtitle-2 font-weight-bold text-primary text-uppercase">
                    {{ group.category }}
                  </span>
                  <VChip size="x-small" variant="tonal" color="primary">
                    {{ group.articles.length }}
                  </VChip>
                </div>
              </VCardText>

              <VDivider class="mx-4 mb-2" />

              <!-- Articles in this category -->
              <div
                v-for="article in group.articles"
                :key="article.id"
                class="px-4 py-3"
                style="border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));"
              >
                <div class="d-flex gap-3">
                  <!-- Thumbnail -->
                  <VAvatar
                    rounded
                    size="72"
                    :image="article.image_url || undefined"
                    color="primary"
                    variant="tonal"
                    class="flex-shrink-0"
                  >
                    <VIcon v-if="!article.image_url" icon="ri-newspaper-line" size="28" />
                  </VAvatar>

                  <!-- Content -->
                  <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-start justify-space-between gap-2">
                      <a
                        :href="article.url"
                        target="_blank"
                        class="text-body-1 font-weight-medium text-high-emphasis"
                        style="line-height: 1.4; text-decoration: none;"
                      >
                        {{ article.title }}
                      </a>
                      <IconBtn
                        size="small"
                        :color="article.is_saved ? 'primary' : 'default'"
                        class="flex-shrink-0"
                        @click="toggleSave(article)"
                      >
                        <VIcon :icon="article.is_saved ? 'ri-bookmark-fill' : 'ri-bookmark-line'" size="18" />
                        <VTooltip activator="parent">{{ article.is_saved ? 'Unsave' : 'Save' }}</VTooltip>
                      </IconBtn>
                    </div>

                    <!-- AI Summary -->
                    <div v-if="article.summary" class="mt-2">
                      <div class="d-flex align-center gap-1 mb-1">
                        <VIcon icon="ri-sparkling-line" size="12" color="warning" />
                        <span class="text-caption text-warning font-weight-medium">AI Summary</span>
                      </div>
                      <p class="text-body-2 text-medium-emphasis mb-0" style="line-height: 1.5;">
                        {{ article.summary }}
                      </p>
                    </div>
                    <VChip v-else size="x-small" color="warning" variant="tonal" class="mt-2">
                      Summary pending
                    </VChip>

                    <!-- Meta -->
                    <div class="d-flex align-center gap-3 mt-2">
                      <span class="text-caption text-disabled">
                        <VIcon icon="ri-time-line" size="12" class="me-1" />
                        {{ article.published_at }}
                      </span>
                      <span v-if="article.source_name" class="text-caption text-disabled">
                        <VIcon icon="ri-global-line" size="12" class="me-1" />
                        {{ article.source_name }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </VCard>
      </VCol>

      <!-- Digest History sidebar -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="pb-0">
            <h5 class="text-h5 mb-1">Send History</h5>
            <p class="text-body-2 text-medium-emphasis mb-0">Your past digest emails</p>
          </VCardText>

          <VDivider class="mt-4" />

          <VCardText v-if="historyLoading">
            <VSkeletonLoader v-for="n in 5" :key="n" type="list-item-two-line" class="mb-1" />
          </VCardText>

          <VList v-else-if="historyLogs.length" lines="two">
            <VListItem
              v-for="log in historyLogs"
              :key="log.id"
              class="px-4"
            >
              <template #prepend>
                <VAvatar :color="statusColor[log.status]" variant="tonal" size="36" rounded>
                  <VIcon :icon="statusIcon[log.status]" size="18" />
                </VAvatar>
              </template>
              <VListItemTitle class="text-capitalize font-weight-medium">
                {{ log.status }}
                <VChip size="x-small" variant="tonal" :color="statusColor[log.status]" class="ms-1">
                  {{ log.article_count }} articles
                </VChip>
              </VListItemTitle>
              <VListItemSubtitle>{{ log.sent_at ?? 'Pending' }}</VListItemSubtitle>
            </VListItem>
          </VList>

          <VCardText v-else class="text-center py-8 text-medium-emphasis">
            <VIcon icon="ri-mail-send-line" size="48" class="mb-3 d-block" />
            <p class="text-body-2 mb-0">No digests sent yet</p>
            <p class="text-caption">Digests are sent automatically at your scheduled time</p>
          </VCardText>

          <!-- Pagination -->
          <template v-if="historyTotal > historyPerPage">
            <VDivider />
            <VCardText class="d-flex justify-center gap-2 py-2">
              <VBtn
                size="small" variant="text" icon="ri-arrow-left-s-line"
                :disabled="historyPage <= 1"
                @click="historyPage--; loadHistory()"
              />
              <span class="text-body-2 d-flex align-center">
                {{ historyPage }} / {{ Math.ceil(historyTotal / historyPerPage) }}
              </span>
              <VBtn
                size="small" variant="text" icon="ri-arrow-right-s-line"
                :disabled="historyPage >= Math.ceil(historyTotal / historyPerPage)"
                @click="historyPage++; loadHistory()"
              />
            </VCardText>
          </template>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
