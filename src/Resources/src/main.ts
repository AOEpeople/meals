import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createI18n } from 'vue-i18n' // import from runtime only

import en from '@/locales/en.json';
import de from '@/locales/de.json';

import '../style/output.css'

import App          from '@/App.vue'
import MealsMain    from '@/views/MealsMain.vue'
import Menu         from '@/views/Menu.vue'
import Dishes       from '@/views/Dishes.vue'
import Categories   from '@/views/Categories.vue'
import TimeSlots    from '@/views/TimeSlots.vue'
import Costs        from '@/views/Costs.vue'
import Finance      from '@/views/Finance.vue'
import Balance      from '@/views/Balance.vue'
import Guest        from '@/views/Guest.vue'

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
        {path: '/',             name: 'Root',       component: MealsMain},
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
vueApp.mount('#app');