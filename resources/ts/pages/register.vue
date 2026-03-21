<script setup lang="ts">
import { VForm } from 'vuetify/components/VForm'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import authV2RegisterIllustrationBorderedDark from '@images/pages/auth-v2-register-illustration-bordered-dark.png'
import authV2RegisterIllustrationBorderedLight from '@images/pages/auth-v2-register-illustration-bordered-light.png'
import authV2RegisterIllustrationDark from '@images/pages/auth-v2-register-illustration-dark.png'
import authV2RegisterIllustrationLight from '@images/pages/auth-v2-register-illustration-light.png'
import authV2RegisterMaskDark from '@images/pages/auth-v2-register-mask-dark.png'
import authV2RegisterMaskLight from '@images/pages/auth-v2-register-mask-light.png'
import { useAuthStore } from '@/stores/auth'

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const authThemeMask = useGenerateImageVariant(authV2RegisterMaskLight, authV2RegisterMaskDark)
const authThemeImg  = useGenerateImageVariant(
  authV2RegisterIllustrationLight,
  authV2RegisterIllustrationDark,
  authV2RegisterIllustrationBorderedLight,
  authV2RegisterIllustrationBorderedDark,
  true,
)

const authStore         = useAuthStore()
const refVForm          = ref<VForm>()
const isPasswordVisible = ref(false)
const isLoading         = ref(false)

const form   = ref({ name: '', email: '', password: '', passwordConfirmation: '', privacyPolicies: false })
const errors = ref<Record<string, string | undefined>>({})

async function register() {
  isLoading.value = true
  errors.value    = {}
  try {
    await authStore.register(form.value.name, form.value.email, form.value.password, form.value.passwordConfirmation)
  }
  catch (err: any) {
    const apiErrors = err?.data?.errors ?? err?.response?._data?.errors
    if (apiErrors) errors.value = Object.fromEntries(Object.entries(apiErrors).map(([k, v]) => [k, (v as string[])[0]]))
    else errors.value = { name: 'Registration failed. Please try again.' }
  }
  finally {
    isLoading.value = false
  }
}

function onSubmit() {
  refVForm.value?.validate().then(({ valid }) => { if (valid) register() })
}
</script>

<template>
  <RouterLink to="/">
    <div class="app-logo auth-logo">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="app-logo-title">{{ themeConfig.app.title }}</h1>
    </div>
  </RouterLink>

  <VRow no-gutters class="auth-wrapper">
    <VCol md="8" class="d-none d-md-flex align-center justify-center position-relative">
      <div class="d-flex align-center justify-center pa-10">
        <img :src="authThemeImg" class="auth-illustration w-100" alt="auth-illustration">
      </div>
      <VImg :src="authThemeMask" class="d-none d-md-flex auth-footer-mask" alt="auth-mask" />
    </VCol>

    <VCol cols="12" md="4" class="auth-card-v2 d-flex align-center justify-center"
      style="background-color: rgb(var(--v-theme-surface));">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-5 pa-lg-7">
        <VCardText>
          <h4 class="text-h4 mb-1">Adventure starts here 🚀</h4>
          <p class="mb-0">Create your AI News Filter account</p>
        </VCardText>

        <VCardText>
          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  autofocus
                  label="Full Name"
                  placeholder="John Doe"
                  :rules="[requiredValidator]"
                  :error-messages="errors.name"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                  placeholder="you@example.com"
                  :rules="[requiredValidator, emailValidator]"
                  :error-messages="errors.email"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  placeholder="············"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.passwordConfirmation"
                  label="Confirm Password"
                  placeholder="············"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :error-messages="errors.password_confirmation"
                />

                <div class="d-flex align-center my-6">
                  <VCheckbox id="privacy-policy" v-model="form.privacyPolicies" inline :rules="[requiredValidator]" />
                  <VLabel for="privacy-policy" style="opacity: 1;">
                    <span class="me-1 text-high-emphasis">I agree to</span>
                    <a href="javascript:void(0)" class="text-primary">privacy policy & terms</a>
                  </VLabel>
                </div>

                <VBtn block type="submit" :loading="isLoading">Sign up</VBtn>
              </VCol>

              <VCol cols="12">
                <div class="text-center text-base">
                  <span class="d-inline-block">Already have an account?</span>
                  <RouterLink class="text-primary d-inline-block" :to="{ name: 'login' }">Sign in instead</RouterLink>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
