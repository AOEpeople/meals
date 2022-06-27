import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createI18n } from 'vue-i18n'
import VueScreen from 'vue-screen'

// Style
import '../style/output.css'

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
import Guest        from '@/views/Guest.vue'

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
        {path: '/guest',        name: 'Guest',      component: Guest},
    ],
})

// Vue initialisation
const VueApp = createApp(App)
VueApp.config.performance = true // enable Vue Devtools
VueApp.use(i18n)
VueApp.use(router)
VueApp.use(VueScreen)
VueApp.mount('#app')