import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './assets/styles/style.css'
import App from './App.vue'
import '@fortawesome/fontawesome-free/css/all.css'
import roteador from './router' 

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(roteador) 
app.mount('#app')