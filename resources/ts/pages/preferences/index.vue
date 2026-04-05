<script setup lang="ts">
import { $api } from '@/utils/api'

definePage({ meta: { action: 'read', subject: 'Preferences' } })

const loading  = ref(true)
const saving   = ref(false)
const saved    = ref(false)

const categories     = ref<any[]>([])
const selectedCatIds = ref<number[]>([])
const digestSetting  = ref({
  digest_enabled: true,
  frequency:      'daily',
  send_time:      '07:00',
  timezone:       'UTC',
  max_articles:   5,
})

const timezones = [
  'UTC', 'Asia/Kolkata', 'Asia/Dubai', 'America/New_York',
  'America/Los_Angeles', 'Europe/London', 'Europe/Paris',
  'Asia/Tokyo', 'Asia/Singapore', 'Australia/Sydney',
]

async function load() {
  loading.value = true
  try {
    const res = await $api('getUserPreferences')
    categories.value     = res.data.categories
    selectedCatIds.value = res.data.categories.filter((c: any) => c.selected).map((c: any) => c.id)
    Object.assign(digestSetting.value, res.data.digest_setting)
    // Normalize send_time to HH:mm
    if (digestSetting.value.send_time?.length > 5)
      digestSetting.value.send_time = digestSetting.value.send_time.slice(0, 5)
  }
  finally { loading.value = false }
}

function toggleCategory(id: number) {
  const idx = selectedCatIds.value.indexOf(id)
  if (idx === -1) selectedCatIds.value.push(id)
  else selectedCatIds.value.splice(idx, 1)
}

function isSelected(id: number) {
  return selectedCatIds.value.includes(id)
}

async function save() {
  saving.value = true
  saved.value  = false
  try {
    await $api('manageUserPreferences', {
      method: 'POST',
      body: {
        category_ids:   selectedCatIds.value,
        digest_setting: digestSetting.value,
      },
    })
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
      <!-- Category Selection -->
      <VCol cols="12" md="8">
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">My News Categories</h5>
            <p class="text-body-2 text-medium-emphasis mb-5">
              Select the topics you want to receive in your daily digest. You can change these anytime.
            </p>

            <VProgressLinear v-if="loading" indeterminate color="primary" class="mb-4" />

            <VRow v-else>
              <VCol
                v-for="cat in categories"
                :key="cat.id"
                cols="6"
                sm="4"
                md="4"
              >
                <VCard
                  :variant="isSelected(cat.id) ? 'elevated' : 'outlined'"
                  :color="isSelected(cat.id) ? 'primary' : undefined"
                  class="cursor-pointer text-center pa-4"
                  :style="isSelected(cat.id) ? '' : 'opacity: 0.7'"
                  @click="toggleCategory(cat.id)"
                >
                  <VIcon
                    :icon="cat.icon || 'ri-newspaper-line'"
                    size="32"
                    :color="isSelected(cat.id) ? 'white' : 'primary'"
                    class="mb-2"
                  />
                  <p
                    class="text-body-2 font-weight-medium mb-0"
                    :class="isSelected(cat.id) ? 'text-white' : ''"
                  >
                    {{ cat.name }}
                  </p>
                  <VIcon
                    v-if="isSelected(cat.id)"
                    icon="ri-checkbox-circle-fill"
                    size="16"
                    color="white"
                    class="mt-1"
                  />
                </VCard>
              </VCol>
            </VRow>

            <p class="text-caption text-medium-emphasis mt-4 mb-0">
              {{ selectedCatIds.length }} categor{{ selectedCatIds.length !== 1 ? 'ies' : 'y' }} selected
            </p>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Digest Settings -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">Digest Settings</h5>
            <p class="text-body-2 text-medium-emphasis mb-5">
              Configure when and how you receive your daily news digest.
            </p>

            <VRow>
              <VCol cols="12">
                <VSwitch
                  v-model="digestSetting.digest_enabled"
                  label="Enable Daily Digest"
                  color="primary"
                  hide-details
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="digestSetting.frequency"
                  label="Frequency"
                  :items="[{ title: 'Daily', value: 'daily' }, { title: 'Weekly', value: 'weekly' }]"
                  :disabled="!digestSetting.digest_enabled"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="digestSetting.send_time"
                  label="Send Time"
                  type="time"
                  :disabled="!digestSetting.digest_enabled"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="digestSetting.timezone"
                  label="Timezone"
                  :items="timezones"
                  :disabled="!digestSetting.digest_enabled"
                />
              </VCol>

              <VCol cols="12">
                <VSlider
                  v-model="digestSetting.max_articles"
                  label="Max Articles per Digest"
                  :min="1"
                  :max="20"
                  :step="1"
                  thumb-label
                  :disabled="!digestSetting.digest_enabled"
                  color="primary"
                />
                <p class="text-caption text-medium-emphasis mt-n2">
                  Up to {{ digestSetting.max_articles }} articles per digest
                </p>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Save Button -->
      <VCol cols="12">
        <VAlert v-if="saved" type="success" variant="tonal" class="mb-4">
          Preferences saved successfully! Your next digest will reflect these changes.
        </VAlert>

        <VBtn
          size="large"
          :loading="saving"
          :disabled="selectedCatIds.length === 0"
          prepend-icon="ri-save-line"
          @click="save"
        >
          Save Preferences
        </VBtn>

        <p v-if="selectedCatIds.length === 0" class="text-caption text-error mt-2">
          Please select at least one category to save.
        </p>
      </VCol>
    </VRow>
  </div>
</template>
