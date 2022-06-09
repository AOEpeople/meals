<template>
  <header class="bg-white shadow-[0_15px_35px_0_#5B788F21]">
    <Disclosure v-slot="{ open }">
      <nav class="grid grid-cols-3 items-center py-5 mx-auto max-w-screen-aoe xl:grid-cols-10" aria-label="Top">
        <div class="justify-self-center xl:hidden" id="dropdown">
          <DisclosureButton class="inline-flex justify-center items-center p-2 -mx-2 text-gray-400 rounded-md hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
            <span class="sr-only">Open menu</span>
            <MenuIcon v-if="!open" class="block w-6 h-6" aria-hidden="true" />
            <XIcon v-else class="block w-6 h-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
        <div class="inline-block justify-self-center xl:justify-self-start xl:col-span-2">
          <router-link to="/">
            <img class="w-auto h-10 cursor-pointer" src="../../images/meals-logo.svg" alt="Meals Logo" />
          </router-link>
        </div>
        <div class="hidden col-span-4 space-x-3 xl:inline-block">
          <router-link v-for="link in navigation"
             :key="link.name"
             :to="link.to"
             class="text-base font-semibold text-primary hover:text-highlight cursor-pointer"
          >
            {{ link.name }}
          </router-link>
        </div>
        <div class="inline-block col-span-4 justify-self-end space-x-4">
          <div class="hidden self-center space-x-2 text-right xl:inline-block">
            <img class="inline-block w-6" src="../../images/person.svg" alt="Person" />
            <a class="text-base font-semibold text-black">
              {{ userName }}
            </a>
          </div>
          <div class="hidden text-right xl:inline-block">
            <router-link class="text-base font-semibold text-black hover:text-highlight" to="/balance">
              Balance:
              <a class="text-primary-2">
                € {{ balance }}
              </a>
            </router-link>
          </div>
          <div class="hidden justify-self-end xl:inline-block">
            <a href="/logout" >
              <img class="inline-block w-6 cursor-pointer" src="../../images/logout.svg" alt="logout" />
            </a>
          </div>
        </div>
      </nav>
      <MobileDropdown :user="user" :navigation="navigation" />
    </Disclosure>
  </header>
</template>

<script setup>
import { Disclosure, DisclosureButton } from '@headlessui/vue'
import { MenuIcon, XIcon } from '@heroicons/vue/outline'
import MobileDropdown from "./navbar/MobileDropdown.vue";

const balance = sessionStorage.getItem('balance')
const userName = sessionStorage.getItem('user')

const navigation = [
  { name: 'Menu',       to: '/menu',        current: false },
  { name: 'Dishes',     to: '/dishes',      current: false },
  { name: 'Categories', to: '/categories',  current: false },
  { name: 'Time Slots', to: '/time-slots',  current: false },
  { name: 'Costs',      to: '/costs',       current: false },
  { name: 'Finance',    to: '/finance',     current: false },
];

</script>