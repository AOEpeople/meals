import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createI18n } from 'vue-i18n'
import VueScreen from 'vue-screen'
import {Vue3ProgressPlugin} from '@marcoschulte/vue3-progress'

// Style
import '../style/output.css'
import '../scss/meals.scss'

// Vue Components
import App          from '@/App.vue'
import Dashboard    from '@/views/Dashboard.vue'
import Menu         from '@/views/Menu.vue'
import Dishes       from '@/views/Dishes.vue'
import Categories   from '@/views/Categories.vue'
import TimeSlots    from '@/views/TimeSlots.vue'
import Costs        from '@/views/Costs.vue'
import Finance      from '@/views/Finance.vue'
import Balance      from '@/views/Balance.vue'
import Guest        from '@/Guest.vue'

// Translation
import en from '@/locales/en.json'
import de from '@/locales/de.json'

const i18n = createI18n({
    locale: navigator.language,
    fallbackLocale: 'en',
    fallbackWarn: false,
    missingWarn: false,
    messages: {
        en,
        de
    }
})

// Routing
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/',             name: 'Dashboard',  component: Dashboard},
        {path: '/menu',         name: 'Menu',       component: Menu},
        {path: '/dishes',       name: 'Dishes',     component: Dishes},
        {path: '/categories',   name: 'Categories', component: Categories},
        {path: '/time-slots',   name: 'Time Slots', component: TimeSlots},
        {path: '/costs',        name: 'Costs',      component: Costs},
        {path: '/finance',      name: 'Finance',    component: Finance},
        {path: '/balance',      name: 'Balance',    component: Balance},
    ],
})

// Vue initialisation
const MainApp = createApp(App)
MainApp.config.performance = true // enable Vue Devtools
MainApp.use(i18n)
MainApp.use(router)
MainApp.use(VueScreen)
MainApp.use(Vue3ProgressPlugin)
MainApp.mount('#app')

// Guest initialisation
const GuestApp = createApp(Guest)
GuestApp.config.performance = true
GuestApp.use(i18n)
GuestApp.mount('#guest')