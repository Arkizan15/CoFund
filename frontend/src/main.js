import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'
import App from './App.vue'
import Router from './router'
import i18n from './i18n'
import './style.css'

const app = createApp(App)

app.use(createPinia())
app.use(PrimeVue)
app.use(Router)
app.use(i18n)
app.use(ToastService)
app.directive('tooltip', Tooltip)

app.mount('#app')