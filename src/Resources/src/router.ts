import { createRouter, createWebHistory } from 'vue-router';
import { userDataStore } from '@/stores/userDataStore';

const Dashboard = () => import('@/views/Dashboard.vue');
const Menu = () => import('@/views/Menu.vue');
const Dishes = () => import('@/views/Dishes.vue');
const Categories = () => import('@/views/Categories.vue');
const TimeSlots = () => import('@/views/TimeSlots.vue');
const Costs = () => import('@/views/Costs.vue');
const Finance = () => import('@/views/Finance.vue');
const Balance = () => import('@/views/Balance.vue');
const Guest = () => import('@/views/Guest.vue');
const NotAllowed = () => import('@/views/NotAllowed.vue');
const PrintableList = () => import('@/views/PrintableList.vue');
const ParticipantList = () => import('@/views/ParticipantsList.vue');
const Weeks = () => import('@/views/Weeks.vue');
const MenuParticipations = () => import('@/views/MenuParticipations.vue');
const CostsSettlement = () => import('@/views/CostsSettlement.vue');
const CashRegister = () => import('@/views/CashRegister.vue');
const Login = () => import('@/views/Login.vue');
const Events = () => import('@/views/Events.vue');
const GuestEvent = () => import('@/views/GuestEvent.vue');

declare module 'vue-router' {
    interface RouteMeta {
        allowedRoles: Array<string>;
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
            path: '/menu/:week/:create?',
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
        {
            path: '/login',
            name: 'Login',
            component: Login,
            meta: {
                allowedRoles: []
            }
        },
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
            path: '/guest/event/:hash',
            name: 'GuestEvent',
            component: GuestEvent,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_USER', 'ROLE_ADMIN', 'ROLE_FINANCE', 'ROLE_GUEST']
            },
            props: true
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
        },
        {
            path: '/cash-register',
            name: 'CashRegister',
            component: CashRegister,
            meta: {
                allowedRoles: ['ROLE_ADMIN', 'ROLE_FINANCE', 'ROLE_KITCHEN_STAFF']
            }
        },
        {
            path: '/costs/settlement/confirm/:hash',
            name: 'CostsSettlement',
            component: CostsSettlement,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_USER', 'ROLE_ADMIN', 'ROLE_FINANCE']
            },
            props: true
        },
        {
            path: '/events',
            name: 'Events',
            component: Events,
            meta: {
                allowedRoles: ['ROLE_KITCHEN_STAFF', 'ROLE_ADMIN']
            }
        }
    ]
});

router.beforeEach((to) => {
    if (userDataStore.getState().roles?.includes('ROLE_GUEST') === true || userDataStore.getState().user === '') {
        if (to.name !== 'Guest' && to.name !== 'GuestEvent' && to.name !== 'Login' && to.name !== 'ParticipantList') {
            return { name: 'Login' };
        }
    } else {
        if (to.name === 'Login') {
            return false;
        }

        if (userDataStore.roleAllowsRoute(String(to.name)) === false) {
            return { name: 'NotAllowed' };
        }
    }
});

export default router;
