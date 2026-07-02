import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import Login from './views/Login.vue'

createApp(Login).use(createPinia()).mount('#app')
