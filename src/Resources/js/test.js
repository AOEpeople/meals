import { createApp } from 'vue'
import App from '../vue/App.vue'
import '../node_modules/aoe-group-web-cd/dist/aoe.min.css'
import '../sass/mealz.scss'

const vueApp = createApp(App);
vueApp.mount('#app');