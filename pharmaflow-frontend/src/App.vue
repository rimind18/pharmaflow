<template>
  <div
    id="app"
    class="min-h-screen bg-slate-50 text-slate-900 antialiased"
  >
    <!-- Public Layout -->
    <template v-if="showPublicLayout">
      <Navbar />

      <main class="min-h-screen">
        <router-view />
      </main>

      <Footer />
    </template>

    <!-- Private Layout -->
    <template v-else>
      <router-view />
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'

const route = useRoute()

const hiddenRoutes = [
  '/login',
  '/register',
  '/staff',
  '/owner',
]

const showPublicLayout = computed(() => {
  return !hiddenRoutes.some(path =>
    route.path.startsWith(path)
  )
})
</script>

<style>
html {
  scroll-behavior: smooth;
}

body {
  margin: 0;
  padding: 0;
  background: #f8fafc;
  font-family:
    Inter,
    ui-sans-serif,
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    'Segoe UI',
    sans-serif;
}

/* Premium scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

#app {
  min-height: 100vh;
}
</style>
