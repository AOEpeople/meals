<template>
  <div class="relative top-0 z-[3] print:hidden">
    <Menu v-slot="{ open }">
      <header class="relative h-[60px] bg-white shadow-[0_15px_35px_0_#5B788F21] xl:h-24">
        <nav
          class="grid h-[inherit] grid-cols-3 content-center items-center xl:mx-auto xl:grid-cols-[repeat(10,minmax(140px,1fr))]"
          :class="[isShowParticipations ? 'max-w-screen' : 'xl:max-w-screen-aoe']"
          aria-label="Top"
        >
          <div
            v-if="isAuthenticated && !isShowParticipations"
            id="dropdown"
            class="ml-6 justify-self-start xl:hidden"
          >
            <MenuButton
              class="-mx-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
            >
              <span class="sr-only">Open menu</span>
              <MenuIcon
                v-if="!open"
                class="block size-6"
                aria-hidden="true"
              />
              <XIcon
                v-else
                class="block size-6"
                aria-hidden="true"
              />
            </MenuButton>
          </div>
          <div
            id="logo"
            class="inline-block xl:col-span-2"
            :class="[isShowParticipations ? 'justify-self-start pl-4' : 'justify-self-center xl:justify-self-start']"
          >
            <router-link to="/">
              <svg
                class="group h-[39px] w-[197px] cursor-pointer"
                viewBox="0 0 179 39"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g class="fill-secondary group-hover:fill-highlight">
                  <path
                    d="M109.455 7.46094H112.741L117.581 21.2932L122.42 7.46094H125.707L118.901 26.3008H116.261L109.455 7.46094ZM107.682 7.46094H110.956L111.551 20.9438V26.3008H107.682V7.46094ZM124.206 7.46094H127.492V26.3008H123.611V20.9438L124.206 7.46094ZM137.106 26.5596C136.02 26.5596 135.045 26.387 134.182 26.042C133.319 25.6883 132.586 25.2009 131.982 24.5798C131.387 23.9587 130.93 23.2384 130.611 22.4189C130.292 21.5908 130.132 20.7109 130.132 19.7793V19.2617C130.132 18.2007 130.283 17.2302 130.585 16.3503C130.887 15.4705 131.318 14.707 131.879 14.0601C132.448 13.4131 133.138 12.9171 133.949 12.572C134.76 12.2183 135.674 12.0415 136.692 12.0415C137.684 12.0415 138.564 12.2054 139.332 12.5332C140.1 12.861 140.742 13.3268 141.26 13.9307C141.786 14.5345 142.183 15.2591 142.45 16.1045C142.718 16.9412 142.852 17.8729 142.852 18.8994V20.4521H131.724V17.9678H139.19V17.6831C139.19 17.1655 139.095 16.704 138.905 16.2986C138.724 15.8845 138.448 15.5567 138.077 15.3152C137.706 15.0736 137.232 14.9529 136.654 14.9529C136.162 14.9529 135.739 15.0607 135.385 15.2764C135.032 15.492 134.743 15.7939 134.519 16.1821C134.303 16.5703 134.139 17.0275 134.027 17.5537C133.923 18.0713 133.872 18.6406 133.872 19.2617V19.7793C133.872 20.34 133.949 20.8576 134.104 21.332C134.268 21.8065 134.497 22.2162 134.79 22.5613C135.092 22.9063 135.455 23.1737 135.877 23.3635C136.309 23.5533 136.796 23.6482 137.339 23.6482C138.012 23.6482 138.638 23.5188 139.216 23.26C139.802 22.9926 140.307 22.5915 140.729 22.0566L142.541 24.0234C142.248 24.4461 141.847 24.8516 141.338 25.2397C140.837 25.6279 140.233 25.9471 139.526 26.1973C138.819 26.4388 138.012 26.5596 137.106 26.5596ZM152.53 23.1436V16.9067C152.53 16.4582 152.457 16.0743 152.31 15.7551C152.164 15.4273 151.935 15.1729 151.625 14.9917C151.323 14.8105 150.93 14.72 150.447 14.72C150.033 14.72 149.675 14.7933 149.373 14.9399C149.071 15.078 148.838 15.2807 148.674 15.5481C148.51 15.8069 148.428 16.1131 148.428 16.4668H144.702C144.702 15.8716 144.84 15.3066 145.116 14.7717C145.392 14.2369 145.793 13.7668 146.319 13.3613C146.846 12.9473 147.471 12.6238 148.196 12.3909C148.929 12.158 149.748 12.0415 150.654 12.0415C151.741 12.0415 152.707 12.2227 153.552 12.585C154.398 12.9473 155.062 13.4907 155.545 14.2153C156.037 14.9399 156.283 15.8457 156.283 16.9326V22.9236C156.283 23.6913 156.33 24.321 156.425 24.8127C156.52 25.2958 156.658 25.7185 156.839 26.0808V26.3008H153.074C152.893 25.9212 152.755 25.4468 152.66 24.8774C152.573 24.2995 152.53 23.7215 152.53 23.1436ZM153.022 17.7737L153.048 19.8828H150.965C150.473 19.8828 150.046 19.9389 149.684 20.051C149.321 20.1632 149.024 20.3228 148.791 20.5298C148.558 20.7282 148.385 20.9611 148.273 21.2285C148.17 21.4959 148.118 21.7892 148.118 22.1084C148.118 22.4276 148.191 22.7166 148.338 22.9753C148.485 23.2255 148.696 23.4239 148.972 23.5706C149.248 23.7086 149.571 23.7776 149.942 23.7776C150.503 23.7776 150.99 23.6654 151.405 23.4412C151.819 23.2169 152.138 22.9408 152.362 22.613C152.595 22.2852 152.716 21.9747 152.724 21.6814L153.708 23.26C153.57 23.6137 153.38 23.9803 153.138 24.3599C152.906 24.7394 152.608 25.0974 152.246 25.4338C151.883 25.7616 151.448 26.0334 150.939 26.249C150.43 26.4561 149.826 26.5596 149.127 26.5596C148.239 26.5596 147.432 26.3827 146.708 26.0291C145.992 25.6667 145.422 25.1707 145 24.541C144.585 23.9027 144.378 23.1781 144.378 22.3672C144.378 21.634 144.516 20.9827 144.792 20.4133C145.069 19.844 145.474 19.3652 146.009 18.9771C146.552 18.5802 147.229 18.2826 148.04 18.0842C148.851 17.8772 149.791 17.7737 150.861 17.7737H153.022ZM163.089 6.42578V26.3008H159.349V6.42578H163.089ZM173.738 22.4319C173.738 22.1645 173.66 21.9229 173.505 21.7073C173.35 21.4916 173.061 21.2932 172.638 21.1121C172.224 20.9223 171.625 20.7498 170.84 20.5945C170.132 20.4392 169.472 20.2451 168.86 20.0122C168.256 19.7707 167.73 19.4817 167.281 19.1453C166.841 18.8088 166.496 18.412 166.246 17.9548C165.996 17.489 165.871 16.9585 165.871 16.3633C165.871 15.7767 165.996 15.2246 166.246 14.707C166.505 14.1895 166.872 13.7323 167.346 13.3354C167.829 12.93 168.416 12.6152 169.106 12.3909C169.804 12.158 170.589 12.0415 171.461 12.0415C172.677 12.0415 173.721 12.2356 174.592 12.6238C175.472 13.012 176.145 13.5468 176.611 14.2283C177.085 14.9011 177.322 15.6689 177.322 16.5315H173.596C173.596 16.1692 173.518 15.8457 173.363 15.561C173.216 15.2677 172.983 15.0391 172.664 14.8752C172.354 14.7027 171.948 14.6165 171.448 14.6165C171.034 14.6165 170.676 14.6898 170.374 14.8364C170.072 14.9744 169.839 15.1642 169.675 15.4058C169.52 15.6387 169.442 15.8975 169.442 16.1821C169.442 16.3978 169.485 16.5919 169.572 16.7644C169.666 16.9283 169.817 17.0793 170.024 17.2173C170.231 17.3553 170.499 17.4847 170.827 17.6055C171.163 17.7176 171.577 17.8211 172.069 17.916C173.078 18.123 173.98 18.3948 174.773 18.7312C175.567 19.059 176.197 19.5076 176.662 20.0769C177.128 20.6376 177.361 21.3752 177.361 22.2896C177.361 22.9106 177.223 23.48 176.947 23.9976C176.671 24.5151 176.274 24.968 175.757 25.3562C175.239 25.7358 174.618 26.0334 173.893 26.249C173.177 26.4561 172.371 26.5596 171.474 26.5596C170.171 26.5596 169.067 26.3267 168.161 25.8608C167.264 25.395 166.583 24.8041 166.117 24.0881C165.66 23.3635 165.431 22.6217 165.431 21.8625H168.963C168.981 22.3715 169.11 22.7812 169.352 23.0918C169.602 23.4023 169.917 23.6266 170.296 23.7646C170.684 23.9027 171.103 23.9717 171.551 23.9717C172.034 23.9717 172.435 23.907 172.755 23.7776C173.074 23.6396 173.315 23.4584 173.479 23.2341C173.652 23.0012 173.738 22.7338 173.738 22.4319Z"
                  />
                </g>
                <g class="fill-primary">
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M18.3612 5.49376C18.1342 5.39522 17.7623 5.39455 17.5352 5.49248L6.22947 10.3667C6.00185 10.4648 5.9911 10.6465 6.2051 10.7709L17.5329 17.3396C17.7472 17.4636 17.9225 17.7671 17.9227 18.0137L17.9297 30.9292C17.9298 31.1762 18.0936 31.2589 18.2937 31.1136L28.122 23.9765C28.3223 23.8312 28.5092 23.5118 28.5378 23.2669L29.9615 11.0116C29.9901 10.7666 29.8276 10.4853 29.6004 10.3864L18.3612 5.49376ZM35.5023 7.05564C35.7314 7.14939 35.8907 7.42567 35.8561 7.66954L33.259 25.9309C33.2244 26.1748 33.0363 26.4988 32.8411 26.651L18.2877 37.9771C18.0926 38.1293 17.7731 38.1293 17.5779 37.9771L3.02584 26.651C2.83049 26.4988 2.64221 26.1748 2.60746 25.9309L0.00469189 7.66924C-0.029759 7.4253 0.129258 7.14939 0.35853 7.05564L17.5306 0.0697548C17.7599 -0.0234642 18.135 -0.0231636 18.3646 0.0701303L35.5023 7.05564ZM53.5096 18.271L51.6892 12.9232C51.6506 12.8107 51.5872 12.8104 51.5479 12.9225L49.6659 18.2716C49.6266 18.3837 49.692 18.4758 49.8115 18.4758H53.3625C53.4816 18.4758 53.5478 18.3834 53.5096 18.271ZM49.1133 7.44267H54.1953C54.3145 7.44267 54.446 7.53364 54.4872 7.64536L61.3095 26.0774C61.3507 26.1888 61.2868 26.28 61.1676 26.28H56.0379C55.9185 26.28 55.794 26.1865 55.7608 26.0724L54.8727 23.0088C54.8395 22.8947 54.7149 22.8012 54.5956 22.8012H48.546C48.4264 22.8012 48.3019 22.8947 48.2689 23.0088L47.3814 26.0724C47.3481 26.1865 47.2235 26.28 47.1041 26.28H42.2181C42.0985 26.28 42.0345 26.1888 42.0753 26.0771L48.8219 7.64566C48.8629 7.53364 48.9939 7.44267 49.1133 7.44267ZM70.6123 22.0736C72.8973 22.0736 73.8509 21.3399 73.8509 16.7971C73.8509 12.0861 72.8059 11.5701 70.5877 11.5701C68.3682 11.5701 67.3235 12.0861 67.3235 16.7971C67.3235 21.3399 68.2941 22.0736 70.6123 22.0736ZM70.5877 7.20075C76.3359 7.20075 78.9006 10.1612 78.9006 16.7978C78.9006 23.5577 76.4146 26.4419 70.5877 26.4419C64.9004 26.4419 62.2525 23.3773 62.2525 16.7978C62.2525 10.1612 64.8233 7.20075 70.5877 7.20075ZM81.9541 26.056V22.1105C81.9541 21.9944 82.0497 21.8993 82.1662 21.8993H96.6216C96.7382 21.8993 96.8336 21.9941 96.8336 22.1102V26.056C96.8336 26.1721 96.7382 26.2672 96.6216 26.2672H82.1662C82.0497 26.2672 81.9541 26.1721 81.9541 26.056ZM82.1627 7.44267H96.6181C96.7347 7.44267 96.8302 7.53746 96.8302 7.65352V11.5987C96.8302 11.7147 96.7347 11.8094 96.6181 11.8094H82.1627C82.0461 11.8094 81.9507 11.7147 81.9507 11.5987V7.65352C81.9507 7.53746 82.0461 7.44267 82.1627 7.44267ZM81.9508 18.827V14.8821C81.9508 14.7661 82.0461 14.6713 82.1627 14.6713H96.6182C96.7347 14.6713 96.8302 14.7661 96.8302 14.8821V18.827C96.8302 18.9434 96.7347 19.0382 96.6182 19.0382H82.1627C82.0461 19.0382 81.9508 18.9434 81.9508 18.827Z"
                  />
                </g>
              </svg>
            </router-link>
          </div>
          <div
            v-if="isAuthenticated && !isShowParticipations"
            class="col-span-4 hidden space-x-3 xl:inline-block"
          >
            <span
              v-for="link in navigation"
              :key="link.name"
            >
              <router-link
                v-if="link.access"
                :to="link.to"
                class="cursor-pointer text-[16px] font-bold leading-[18px] text-primary hover:text-highlight"
              >
                {{ t(link.name) }}
              </router-link>
            </span>
          </div>
          <div
            v-if="isAuthenticated && !isShowParticipations"
            class="col-span-4 inline-block space-x-4 justify-self-end"
          >
            <div class="hidden space-x-2 self-center text-right xl:inline-block">
              <Icons
                icon="person-outline"
                class="inline-block w-6 fill-primary"
              />
              <span class="text-[14px] font-bold leading-[22px] text-black">
                {{ user }}
              </span>
            </div>
            <div
              id="balance"
              class="hidden text-right xl:inline-block"
            >
              <router-link
                class="text-[14px] font-bold leading-[22px] text-black"
                to="/balance"
              >
                {{ t('header.balance') }}:
                <span class="text-primary-2"> € {{ balanceString }} </span>
              </router-link>
            </div>
            <div class="hidden justify-self-end xl:inline-block">
              <div @click="logout">
                <Icons
                  icon="logout"
                  class="inline-block w-6 cursor-pointer fill-primary hover:fill-secondary"
                />
              </div>
            </div>
          </div>
          <ErrorTrafficLight
            v-if="isShowParticipations"
            :error-states="[nextThreeDaysState.error, getShowParticipationsError]"
            class="col-end-4 inline-block xl:col-end-12"
            :class="[isShowParticipations ? 'justify-self-end pr-4' : 'justify-self-center xl:justify-self-start']"
          />
        </nav>
      </header>
      <MobileDropdown3
        v-if="isAuthenticated && !isShowParticipations"
        :open="open"
        :userName="user"
        :balance="balanceString"
        :navigation="navigation"
        @logout="logout"
      />
    </Menu>
  </div>
</template>

<script setup lang="ts">
import { Menu, MenuButton } from '@headlessui/vue';
import {
  MenuIcon,
  XIcon,
  CalendarIcon,
  CalculatorIcon,
  CashIcon,
  BookmarkIcon,
  ClockIcon,
  CurrencyEuroIcon
} from '@heroicons/vue/outline';
import Icons from '@/components/misc/Icons.vue';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import MobileDropdown3 from '@/components/navbar/MobileDropdown3.vue';
import { userDataStore } from '@/stores/userDataStore';
import { useRoute } from 'vue-router';
import ErrorTrafficLight from './navbar/ErrorTrafficLight.vue';
import { getNextThreeDays } from '@/api/getMealsNextThreeDays';
import { getShowParticipations } from '@/api/getShowParticipations';
import EventIcon from './misc/EventIcon.vue';
import MealIcon from './menu/MealIcon.vue';

const { t, locale } = useI18n();

withDefaults(
  defineProps<{
    guest?: boolean;
  }>(),
  {
    guest: false
  }
);

const { nextThreeDaysState } = getNextThreeDays();
const { loadedState } = getShowParticipations();

const route = useRoute();

const isShowParticipations = computed(() => {
  return route.path === '/show/participations';
});

const getShowParticipationsError = computed(() => loadedState.error !== '');

const balanceString = computed(() => userDataStore.balanceToLocalString(locale.value));

const user = computed(() => userDataStore.getState().user ?? '');
const isAuthenticated = computed(() => {
  return !userDataStore.getState().roles.includes('ROLE_GUEST');
});

const navigation = computed(() => {
  return [
    {
      name: 'header.navigation.menu',
      to: '/weeks',
      icon: CalendarIcon,
      access: userDataStore.roleAllowsRoute('Weeks')
    },
    {
      name: 'header.navigation.dishes',
      to: '/dishes',
      icon: MealIcon,
      access: userDataStore.roleAllowsRoute('Dishes')
    },
    {
      name: 'header.navigation.categories',
      to: '/categories',
      icon: BookmarkIcon,
      access: userDataStore.roleAllowsRoute('Categories')
    },
    {
      name: 'header.navigation.slots',
      to: '/time-slots',
      icon: ClockIcon,
      access: userDataStore.roleAllowsRoute('Time Slots')
    },
    {
      name: 'header.navigation.events',
      to: '/events',
      icon: EventIcon,
      access: userDataStore.roleAllowsRoute('Events')
    },
    {
      name: 'header.navigation.prices',
      to: '/prices',
      icon: CurrencyEuroIcon,
      access: userDataStore.roleAllowsRoute('Prices')
    },
    { name: 'header.navigation.costs', to: '/costs', icon: CashIcon, access: userDataStore.roleAllowsRoute('Costs') },
    {
      name: 'header.navigation.finance',
      to: '/finance',
      icon: CalculatorIcon,
      access: userDataStore.roleAllowsRoute('Finance')
    }
  ];
});

function logout() {
  sessionStorage.clear();
  window.location.href = '/logout';
}
</script>

<style scoped>
#balance:hover a {
  @apply text-highlight;
}

#balance:hover a span {
  @apply text-secondary;
}
</style>
