<script setup lang="ts">
import { VForm } from 'vuetify/components/VForm'
import { themeConfig } from '@themeConfig'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2LoginMaskDark from '@images/pages/auth-v2-login-mask-dark.png'
import authV2LoginMaskLight from '@images/pages/auth-v2-login-mask-light.png'
import { useAuthStore } from '@/stores/auth'

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const authThemeImg = useGenerateImageVariant(
  authV2LoginIllustrationLight,
  authV2LoginIllustrationDark,
  authV2LoginIllustrationBorderedLight,
  authV2LoginIllustrationBorderedDark,
  true,
)
const authThemeMask = useGenerateImageVariant(authV2LoginMaskLight, authV2LoginMaskDark)

const authStore         = useAuthStore()
const refVForm          = ref<VForm>()
const isPasswordVisible = ref(false)
const isLoading         = ref(false)

const credentials = ref({ email: '', password: '' })
const errors      = ref<Record<string, string | undefined>>({ email: undefined, password: undefined })

async function login() {
  isLoading.value = true
  errors.value    = { email: undefined, password: undefined }
  try {
    await authStore.login(credentials.value.email, credentials.value.password)
  }
  catch (err: any) {
    const apiErrors = err?.data?.errors ?? err?.response?._data?.errors
    if (apiErrors)
      errors.value = { email: apiErrors.email?.[0], password: apiErrors.password?.[0] }
    else
      errors.value = { email: 'Invalid email or password.' }
  }
  finally {
    isLoading.value = false
  }
}

function onSubmit() {
  refVForm.value?.validate().then(({ valid }) => { if (valid) login() })
}
</script>

<template>
  <RouterLink to="/">
    <div class="auth-logo app-logo">
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
          <h4 class="text-h4 mb-1">
            Welcome to <span class="text-capitalize">{{ themeConfig.app.title }}!</span> 👋🏻
          </h4>
          <p class="mb-0">Sign in to your account to continue</p>
        </VCardText>

        <VCardText>
          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="credentials.email"
                  label="Email"
                  placeholder="you@example.com"
                  type="email"
                  autofocus
                  :rules="[requiredValidator, emailValidator]"
                  :error-messages="errors.email"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="credentials.password"
                  label="Password"
                  placeholder="············"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center flex-wrap justify-space-between my-6 gap-x-2">
                  <RouterLink class="text-primary" :to="{ name: 'forgot-password' }">
                    Forgot Password?
                  </RouterLink>
                </div>

                <VBtn block type="submit" :loading="isLoading">Login</VBtn>
              </VCol>

              <VCol cols="12" class="text-body-1 text-center">
                <span class="d-inline-block">New on our platform?</span>
                <RouterLink class="text-primary ms-1 d-inline-block text-body-1" :to="{ name: 'register' }">
                  Create an account
                </RouterLink>
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
