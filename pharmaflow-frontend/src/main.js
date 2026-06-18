import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'

import './style.css'

import { useAuthStore } from '@/stores/auth'

const app = createApp(App)

const pinia = createPinia()

app.use(pinia)

const authStore = useAuthStore()

authStore.initializeAuth().then(() => {
  app.use(router)
  app.use(ElementPlus)
  app.mount('#app')
})