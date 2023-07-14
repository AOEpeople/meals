import Dashboard     from "@/views/Dashboard.vue";
import Menu          from "@/views/Menu.vue";
import Dishes        from "@/views/Dishes.vue";
import Categories    from "@/views/Categories.vue";
import TimeSlots     from "@/views/TimeSlots.vue";
import Costs         from "@/views/Costs.vue";
import Finance       from "@/views/Finance.vue";
import Balance       from "@/views/Balance.vue";
import Guest         from "@/views/Guest.vue";
import NotAllowed    from "@/views/NotAllowed.vue";
import PrintableList from "@/views/PrintableList.vue";
import ParticipantList from "@/views/ParticipantsList.vue";
import Weeks         from "@/views/Weeks.vue";
import MenuParticipations from "./views/MenuParticipations.vue";

import { createRouter, createWebHistory } from "vue-router";
import { userDataStore }                  from "@/stores/userDataStore";

declare module 'vue-router' {
    interface RouteMeta {
        allowedRoles: Array<string>
    }
}

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'Dashboard',
            component: Dashboard,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_USER', 'ROLE_ADMIN', 'ROLE_FINANCE']
            }
        },
        {
            path: '/weeks',
            name: 'Weeks',
            component: Weeks,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            }
        },
        {
            path: '/menu/:week',
            name: 'Menu',
            component: Menu,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            },
            props: true
        },
        {
            path: '/participations/:week/edit',
            name: 'MenuParticipations',
            component: MenuParticipations,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            },
            props: true
        },
        {
            path: '/dishes',
            name: 'Dishes',
            component: Dishes,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            }
        },
        {
            path: '/categories',
            name: 'Categories',
            component: Categories,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            }
        },
       /* {
            path: '/login',
            name: 'Login',
            component: Login,
            meta: {
                allowedRoles: []
            }
        },*/
        {
            path: '/time-slots',
            name: 'Time Slots',
            component: TimeSlots,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            }
        },
        {
            path: '/costs',
            name: 'Costs',
            component: Costs,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN', 'ROLE_FINANCE']
            }
        },
        {
            path: '/finance',
            name: 'Finance',
            component: Finance,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN', 'ROLE_FINANCE']
            }
        },
        {
            path: '/balance',
            name: 'Balance',
            component: Balance,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_USER', 'ROLE_ADMIN', 'ROLE_FINANCE']
            }
        },
        {
            path: '/guest/:hash',
            name: 'Guest',
            component: Guest,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_USER', 'ROLE_ADMIN', 'ROLE_FINANCE', 'ROLE_GUEST']
            }
        },
        {
            path: '/notAllowed',
            name: 'NotAllowed',
            component: NotAllowed,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_USER', 'ROLE_ADMIN', 'ROLE_FINANCE', 'ROLE_GUEST']
            }
        },
        {
            path: '/print/participations',
            name: 'PrintableList',
            component: PrintableList,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            }
        },
        {
            path: '/show/participations',
            name: 'ParticipantList',
            component: ParticipantList,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN', 'ROLE_GUEST', 'IS_AUTHENTICATED_ANONYMOUSLY']
            }
        }
    ],
})

router.beforeEach((to) => {
    if (userDataStore.getState().roles.includes('ROLE_GUEST') === true) {
        if (to.name !== 'Guest' && to.name !== 'Login' && to.name !== 'ParticipantList') {
            return { name: 'Login' }
        }
    } else {
        if (to.name === 'Login') {
            return false
        }

        if (userDataStore.roleAllowsRoute(String(to.name)) === false) {
            return { name: 'NotAllowed' }
        }
    }
})

export default router