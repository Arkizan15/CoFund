import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'
import i18n from './i18n'
import App from './App.vue'
import Router from './router'
import './style.css'

const app = createApp(App)

app.use(createPinia())
app.use(PrimeVue)
app.use(i18n)
app.use(Router)
app.use(ToastService)
app.directive('tooltip', Tooltip)

app.mount('#app')