<template>
  <DisclosurePanel
    v-slot="{ close }"
    class="grid border-t-2 border-gray-200 bg-white hover:overflow-scroll sm:grid-cols-2 xl:hidden"
  >
    <div class="pb-3 pt-2">
      <router-link
        v-for="link in props.navigation"
        :key="link.name"
        :to="link.to"
        :class="[
          link.to === $route.path
            ? 'border-primary bg-indigo-50 text-primary'
            : 'border-transparent text-gray-600 hover:border-highlight hover:text-highlight',
          'block border-l-4 py-2 pl-3 pr-4 text-base font-medium hover:bg-gray-200'
        ]"
        @click="close()"
      >
        {{ t(link.name) }}
      </router-link>
    </div>
    <div class="border-t border-gray-200 pb-3 pt-4 sm:border-l sm:border-t-0">
      <div class="flex items-center px-4 py-2">
        <div class="shrink-0">
          <Icons
            icon="person-outline"
            class="inline-block w-6 fill-primary"
          />
        </div>
        <div class="ml-3">
          <div class="text-base font-medium text-gray-800">
            {{ userName }}
          </div>
        </div>
      </div>
      <router-link
        to="/balance"
        :class="['/balance' === $route.path
                   ? 'border-primary bg-indigo-50 text-primary'
                   : 'border-transparent text-gray-600 hover:border-highlight hover:text-highlight',
                 'block border-l-4 py-2 pl-3 pr-4 text-base font-medium hover:bg-gray-200'
        ]"
        @click="close()"
      >
        {{ t('header.balance') }}:
        <a class="text-primary hover:text-primary">
          € {{ balance }}
        </a>
      </router-link>
      <span
        class="block cursor-pointer px-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-100 hover:text-highlight"
        @click="changeLocale"
      >
        {{ t('changeLanguage') }}
      </span>
      <DisclosureButton
        as="a"
        href="/logout"
        class="block px-4 py-2 text-base font-medium capitalize text-gray-600 hover:bg-gray-100 hover:text-highlight"
      >
        {{ t('logout') }}
      </DisclosureButton>
    </div>
  </DisclosurePanel>
</template>

<script setup>
import { DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import Icons from '@/components/misc/Icons.vue'
import { useI18n } from "vue-i18n";

const { t, locale } = useI18n();

const changeLocale = () => {
  locale.value = locale.value.substring(0, 2) === 'en' ? 'de' : 'en';
}

const props = defineProps([
    'userName',
    'balance',
    'navigation',
]);

</script>
