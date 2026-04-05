<script setup lang="ts">
import { $api } from '@/utils/api'

definePage({ meta: { action: 'update', subject: 'Digest' } })

const loading = ref(true)
const saving  = ref(false)
const saved   = ref(false)

const form = ref({
  digest_enabled: true,
  frequency:      'daily',
  send_time:      '07:00',
  timezone:       'UTC',
  max_articles:   5,
})

const timezones = [
  'UTC', 'Asia/Kolkata', 'Asia/Dubai', 'Asia/Karachi',
  'America/New_York', 'America/Los_Angeles', 'America/Chicago',
  'Europe/London', 'Europe/Paris', 'Europe/Berlin',
  'Asia/Tokyo', 'Asia/Singapore', 'Asia/Shanghai',
  'Australia/Sydney', 'Pacific/Auckland',
]

const frequencyOptions = [
  { title: 'Daily — receive every day', value: 'daily' },
  { title: 'Weekly — receive once a week', value: 'weekly' },
]

async function load() {
  loading.value = true
  try {
    const res = await $api('getUserPreferences')
    const s   = res.data.digest_setting
    Object.assign(form.value, s)
    if (form.value.send_time?.length > 5)
      form.value.send_time = form.value.send_time.slice(0, 5)
  }
  finally { loading.value = false }
}

async function save() {
  saving.value = true
  saved.value  = false
  try {
    await $api('manageDigestSettings', { method: 'POST', body: form.value })
    saved.value = true
    setTimeout(() => { saved.value = false }, 3000)
  }
  finally { saving.value = false }
}

onMounted(load)
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12" md="6">
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">Digest Settings</h5>
            <p class="text-body-2 text-medium-emphasis mb-6">
              Configure when and how you receive your personalised news digest.
            </p>

            <VProgressLinear v-if="loading" indeterminate color="primary" class="mb-4" />

            <VRow v-else>
              <!-- Enable toggle -->
              <VCol cols="12">
                <VCard variant="outlined" class="pa-4">
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <p class="text-body-1 font-weight-medium mb-0">Enable Daily Digest</p>
                      <p class="text-body-2 text-medium-emphasis mb-0">
                        Receive a curated news digest via email
                      </p>
                    </div>
                    <VSwitch
                      v-model="form.digest_enabled"
                      color="primary"
                      hide-details
                      inset
                    />
                  </div>
                </VCard>
              </VCol>

              <!-- Frequency -->
              <VCol cols="12">
                <VSelect
                  v-model="form.frequency"
                  label="Frequency"
                  :items="frequencyOptions"
                  :disabled="!form.digest_enabled"
                  prepend-inner-icon="ri-calendar-line"
                />
              </VCol>

              <!-- Send time -->
              <VCol cols="12" sm="6">
                <VTextField
                  v-model="form.send_time"
                  label="Send Time"
                  type="time"
                  :disabled="!form.digest_enabled"
                  prepend-inner-icon="ri-time-line"
                  hint="Time in your selected timezone"
                  persistent-hint
                />
              </VCol>

              <!-- Timezone -->
              <VCol cols="12" sm="6">
                <VSelect
                  v-model="form.timezone"
                  label="Timezone"
                  :items="timezones"
                  :disabled="!form.digest_enabled"
                  prepend-inner-icon="ri-earth-line"
                />
              </VCol>

              <!-- Max articles -->
              <VCol cols="12">
                <p class="text-body-2 font-weight-medium mb-2">
                  Articles per Digest: <strong>{{ form.max_articles }}</strong>
                </p>
                <VSlider
                  v-model="form.max_articles"
                  :min="1"
                  :max="20"
                  :step="1"
                  thumb-label="always"
                  :disabled="!form.digest_enabled"
                  color="primary"
                  track-color="primary"
                />
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">1 article</span>
                  <span class="text-caption text-medium-emphasis">20 articles</span>
                </div>
              </VCol>

              <!-- Save -->
              <VCol cols="12">
                <VAlert v-if="saved" type="success" variant="tonal" class="mb-4">
                  Settings saved! Your digest will be sent at {{ form.send_time }} ({{ form.timezone }}).
                </VAlert>

                <VBtn
                  :loading="saving"
                  prepend-icon="ri-save-line"
                  @click="save"
                >
                  Save Settings
                </VBtn>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Info card -->
      <VCol cols="12" md="6">
        <VCard variant="outlined">
          <VCardText>
            <h6 class="text-h6 mb-4">
              <VIcon icon="ri-information-line" class="me-2" color="info" />
              How Digests Work
            </h6>

            <VTimeline side="end" density="compact" truncate-line="both">
              <VTimelineItem dot-color="primary" size="small">
                <p class="text-body-2 font-weight-medium mb-0">Articles Fetched</p>
                <p class="text-caption text-medium-emphasis mb-0">
                  News is fetched every 3 hours from NewsAPI and GNews
                </p>
              </VTimelineItem>

              <VTimelineItem dot-color="warning" size="small">
                <p class="text-body-2 font-weight-medium mb-0">AI Summarization</p>
                <p class="text-caption text-medium-emphasis mb-0">
                  Gemini AI summarizes each article into 2-3 sentences
                </p>
              </VTimelineItem>

              <VTimelineItem dot-color="success" size="small">
                <p class="text-body-2 font-weight-medium mb-0">Digest Sent</p>
                <p class="text-caption text-medium-emphasis mb-0">
                  At your scheduled time, a digest email is sent with your top articles
                </p>
              </VTimelineItem>

              <VTimelineItem dot-color="info" size="small">
                <p class="text-body-2 font-weight-medium mb-0">Read Anytime</p>
                <p class="text-caption text-medium-emphasis mb-0">
                  View today's digest here anytime, save articles for later
                </p>
              </VTimelineItem>
            </VTimeline>

            <VDivider class="my-4" />

            <div class="d-flex align-center gap-2 mb-2">
              <VIcon icon="ri-mail-line" size="16" color="primary" />
              <span class="text-body-2">
                Next digest: <strong>{{ form.send_time }}</strong> ({{ form.timezone }})
              </span>
            </div>
            <div class="d-flex align-center gap-2">
              <VIcon icon="ri-newspaper-line" size="16" color="primary" />
              <span class="text-body-2">
                Up to <strong>{{ form.max_articles }}</strong> articles per digest
              </span>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
