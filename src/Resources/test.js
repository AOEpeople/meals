import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import './style/output.css'

import App          from '@/App.vue'
import MealsMain    from '@/view/MealsMain.vue'
import Menu         from '@/view/Menu.vue'
import Dishes       from '@/view/Dishes.vue'
import Categories   from '@/view/Categories.vue'
import TimeSlots    from '@/view/TimeSlots.vue'
import Costs        from '@/view/Costs.vue'
import Finance      from '@/view/Finance.vue'
import Balance      from '@/view/Balance.vue'
import Guest        from '@/view/Guest.vue'

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
vueApp.use(router);
vueApp.mount('#app');