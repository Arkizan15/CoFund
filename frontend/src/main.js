import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import App from './App.vue'
import './style.css'
import Login from './views/Login.vue'
import Register from './views/Register.vue'


const app = createApp(Register)

app.use(createPinia())
app.use(PrimeVue)

app.mount('#app')