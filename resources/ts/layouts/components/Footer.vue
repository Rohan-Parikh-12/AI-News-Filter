<template>
  <div class="h-100 d-flex align-center justify-space-between text-medium-emphasis">
    <!-- Left: copyright + made with love -->
    <div class="d-flex align-center text-base">
      &copy; {{ new Date().getFullYear() }}
      <span class="mx-1">AI News Filter</span>
      &mdash; Made with
      <VIcon icon="ri-heart-fill" color="error" size="1.1rem" class="mx-1" />
      by
      <a
        href="https://github.com/Rohan-Parikh-12"
        target="_blank"
        rel="noopener noreferrer"
        class="text-primary ms-1 font-weight-medium"
      >
        Rohan Parikh
      </a>
    </div>

    <!-- Right: powered by -->
    <span class="d-none d-md-flex align-center gap-x-1 text-body-2">
      Powered by
      <VIcon icon="ri-sparkling-line" color="warning" size="1rem" class="mx-1" />
      Gemini AI
    </span>
  </div>
</template>
