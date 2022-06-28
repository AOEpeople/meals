import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createI18n } from 'vue-i18n' // import from runtime only
import VueScreen from 'vue-screen'

import en from '@/locales/en.json';
import de from '@/locales/de.json';

import '../style/output.css'
import '../scss/meals.scss'

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
import {Vue3ProgressPlugin} from '@marcoschulte/vue3-progress'

const i18n = createI18n({
    locale: navigator.language,
    fallbackLocale: 'en',
    messages: {
        en,
        de
    }
})

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

const vueApp = createApp(App);
vueApp.use(i18n);
vueApp.use(router);
vueApp.use(VueScreen);
vueApp.use(Vue3ProgressPlugin);
vueApp.mount('#app');