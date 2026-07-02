import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import Login from './views/Login.vue'
import PrimeVue from 'primevue/config'

createApp(Login).use(PrimeVue).use(createPinia()).mount('#app')
 