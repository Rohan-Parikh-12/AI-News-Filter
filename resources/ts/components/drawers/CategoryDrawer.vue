<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components/VForm'
import { useCategoryStore } from '@/stores/category'
import type { Category } from '@/types/category'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'saved'): void
}
interface Props { isDrawerOpen: boolean }

const props = defineProps<Props>()
const emit  = defineEmits<Emit>()

const store    = useCategoryStore()
const refForm  = ref<VForm>()
const isSaving = ref(false)
const errorMsg = ref('')

const form = ref({
  id: undefined as number | undefined,
  name: '',
  api_keyword: '',
  description: '',
  icon: '',
  sort_order: 0,
  status: '1' as '0' | '1' | '2',
})

const isEditMode = computed(() => !!form.value.id)

const statusOptions = [
  { title: 'Active',   value: '1' },
  { title: 'Inactive', value: '2' },
  { title: 'Archive',  value: '0' },
]

function open(category?: Category) {
  if (category) {
    form.value = {
      id: category.id, name: category.name, api_keyword: category.api_keyword,
      description: category.description ?? '', icon: category.icon ?? '',
      sort_order: category.sort_order, status: category.status,
    }
  }
  else {
    form.value = { id: undefined, name: '', api_keyword: '', description: '', icon: '', sort_order: 0, status: '1' }
  }
  errorMsg.value = ''
  nextTick(() => refForm.value?.resetValidation())
}

defineExpose({ open })

function closeNavigationDrawer() {
  emit('update:isDrawerOpen', false)
  nextTick(() => { refForm.value?.reset(); refForm.value?.resetValidation() })
}

async function onSubmit() {
  const { valid } = await refForm.value!.validate()
  if (!valid) return
  isSaving.value = true
  errorMsg.value = ''
  try {
    await store.manageCategory(form.value)
    emit('saved')
    emit('update:isDrawerOpen', false)
    nextTick(() => { refForm.value?.reset(); refForm.value?.resetValidation() })
  }
  catch (e: any) {
    errorMsg.value = e?.data?.message ?? e?.data?.errors?.name?.[0] ?? 'Something went wrong.'
  }
  finally { isSaving.value = false }
}
</script>

<template>
  <VNavigationDrawer
    data-allow-mismatch
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="val => emit('update:isDrawerOpen', val)"
  >
    <AppDrawerHeaderSection
      :title="isEditMode ? 'Edit Category' : 'Add Category'"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm ref="refForm" v-model:is-form-valid="isSaving" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  :rules="[requiredValidator]"
                  label="Category Name"
                  placeholder="e.g. Technology"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.api_keyword"
                  :rules="[requiredValidator]"
                  label="API Keyword"
                  placeholder="e.g. technology"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="form.description"
                  label="Description"
                  placeholder="Brief description..."
                  rows="3"
                />
              </VCol>

              <VCol cols="6">
                <VTextField
                  v-model="form.icon"
                  label="Icon"
                  placeholder="ri-computer-line"
                />
              </VCol>

              <VCol cols="6">
                <VTextField
                  v-model.number="form.sort_order"
                  label="Sort Order"
                  type="number"
                  placeholder="0"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="form.status"
                  :items="statusOptions"
                  label="Status"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol v-if="errorMsg" cols="12">
                <VAlert type="error" variant="tonal" closable @click:close="errorMsg = ''">
                  {{ errorMsg }}
                </VAlert>
              </VCol>

              <VCol cols="12">
                <VBtn type="submit" class="me-4" :loading="isSaving">
                  {{ isEditMode ? 'Update' : 'Submit' }}
                </VBtn>
                <VBtn type="reset" variant="outlined" color="error" @click="closeNavigationDrawer">
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
