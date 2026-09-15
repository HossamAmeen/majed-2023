import '@fontsource/cairo/400.css'
import '@fontsource/cairo/600.css'
import '@fontsource/cairo/700.css'
import 'bootstrap/dist/css/bootstrap.rtl.min.css'
import 'bootstrap-icons/font/bootstrap-icons.min.css'
import './assets/main.css'

import { createPinia } from 'pinia'
import { createApp } from 'vue'

import { setSessionExpiredHandler } from './api/http'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

setSessionExpiredHandler(() => {
  useAuthStore(pinia).forgetSession()
  const current = router.currentRoute.value
  if (!current.meta.public) {
    router.replace({ name: 'login', query: { next: current.fullPath } })
  }
})

app.mount('#app')
