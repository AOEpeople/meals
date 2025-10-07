<template>
  <transition
    enter-active-class="transition duration-150 ease-out"
    enter-from-class="transform -translate-x-20 opacity-0"
    enter-to-class="transform translate-x-0 opacity-100"
    leave-active-class="transition duration-110 ease-out"
    leave-from-class="transform translate-x-0 opacity-100"
    leave-to-class="transform -translate-x-20 opacity-0"
  >
    <MenuItems class="absolute bg-white xl:hidden">
      <div class="flex min-h-0 flex-1 flex-col border-r border-gray-200 bg-white">
        <div class="flex flex-1 flex-col overflow-y-auto pb-4 pt-5">
          <nav
            class="mt-2 flex-1 space-y-1 bg-white px-2"
            aria-label="Sidebar"
          >
            <MenuItem
              v-for="item in navigation"
              v-slot="{ close }"
              :key="item.name"
              as="div"
            >
              <router-link
                v-if="item.access"
                :to="item.to"
                :class="[
                  item.to === route.path
                    ? 'bg-gray-100 text-gray-900 hover:bg-gray-100 hover:text-gray-900'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                  'group flex items-center rounded-md p-2 text-sm font-medium'
                ]"
                @click="close()"
              >
                <component
                  :is="item.icon"
                  :class="[
                    item.to === route.path ? 'text-highlight' : 'text-primary group-hover:text-highlight',
                    'mr-3 size-6 shrink-0'
                  ]"
                  aria-hidden="true"
                />
                <span class="flex-1">{{ t(item.name) }}</span>
              </router-link>
            </MenuItem>
          </nav>
        </div>
        <div class="flex flex-1 flex-col overflow-y-auto border-t border-gray-200 pb-4 pt-5">
          <nav
            class="flex-1 space-y-1 bg-white px-2"
            aria-label="Sidebar"
          >
            <div class="group flex items-center rounded-md p-2 text-sm font-medium text-gray-600">
              <Icons
                icon="person-outline"
                class="mr-3 size-6 shrink-0 fill-primary"
              />
              <span class="flex-1">{{ userName }}</span>
            </div>
            <MenuItem
              v-slot="{ close }"
              as="div"
            >
              <router-link
                to="/balance"
                :class="[
                  '/balance' === route.path
                    ? 'bg-gray-100 text-gray-900 hover:bg-gray-100 hover:text-gray-900'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                  'group flex items-center rounded-md p-2 text-sm font-medium'
                ]"
                @click="close()"
              >
                <CurrencyEuroIcon
                  :class="[
                    '/balance' === route.path ? 'text-highlight' : 'text-primary group-hover:text-highlight',
                    'mr-3 size-6 shrink-0'
                  ]"
                  aria-hidden="true"
                />
                <span class="flex-1">{{ balance }}</span>
              </router-link>
            </MenuItem>
            <div
              class="group flex cursor-pointer items-center rounded-md p-2 text-sm font-medium text-gray-600 hover:cursor-pointer hover:bg-gray-50 hover:text-gray-900"
              @click="changeLocale"
            >
              <Icons
                icon="flag"
                class="mr-3 size-6 shrink-0 fill-primary"
              />
              <span class="flex-1">{{ t('changeLanguage') }}</span>
            </div>
            <div
              class="group flex items-center rounded-md p-2 text-sm font-medium text-gray-600 hover:cursor-pointer hover:bg-gray-50 hover:text-gray-900"
              @click="() => emits('logout')"
            >
              <Icons
                icon="logout"
                class="mr-3 size-6 shrink-0 fill-primary"
              />
              <span class="flex-1">{{ t('logout') }}</span>
            </div>
          </nav>
        </div>
      </div>
    </MenuItems>
  </transition>
</template>

<script setup lang="ts">
import { MenuItems, MenuItem } from '@headlessui/vue';
import { CurrencyEuroIcon } from '@heroicons/vue/outline';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import Icons from '../misc/Icons.vue';
import { type INavigation } from '@/interfaces/INavigation';

const route = useRoute();
const { t, locale } = useI18n();

const changeLocale = () => {
  locale.value = locale.value.substring(0, 2) === 'en' ? 'de' : 'en';
};
const emits = defineEmits(['logout']);

defineProps<{
  userName: string;
  balance: string;
  navigation: INavigation[];
}>();
</script>
