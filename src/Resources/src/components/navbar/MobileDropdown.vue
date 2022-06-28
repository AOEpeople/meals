<template>
    <DisclosurePanel class="xl:hidden bg-white" v-slot="{ close }">
      <div class="pt-2 pb-3">
        <router-link
         v-for="link in props.navigation"
          :to="link.to"
          :key="link.name"
          v-html="t(link.name)"
          @click="close()"
          :class="[
              link.current
              ? 'bg-indigo-50 border-primary text-primary'
              : 'border-transparent text-gray-600 hover:border-highlight hover:text-highlight',
               'block pl-3 pr-4 py-2 border-l-4 hover:bg-gray-200 text-base font-medium cursor-pointer'
          ]"
        >
        </router-link>
      </div>
      <div class="pt-4 pb-3 border-t border-gray-200">
        <div class="flex items-center px-4">
          <div class="flex-shrink-0">
            <Icons icon="person-outline" box="0 0 10 10" class="w-[10px] h-[10px] fill-primary" />
          </div>
          <div class="ml-3">
            <div class="text-base font-medium text-gray-800">{{ userName }}</div>
          </div>
        </div>
        <span class="block cursor-pointer px-4 py-2 text-base font-medium text-gray-500 hover:text-highlight hover:bg-gray-100" @click="changeLocale">
          {{ t('changeLanguage') }}
        </span>
        <div class="mt-3 space-y-1">
          <router-link @click="close()" to="/balance" class="block cursor-pointer px-4 py-2 text-base font-medium text-gray-500 hover:text-highlight hover:bg-gray-100">
            {{ t('header.balance') }}:
            <a class="text-primary hover:text-primary">
              € {{ balance }}
            </a>
          </router-link>

          <DisclosureButton as="a" href="/logout" class="block px-4 py-2 text-base font-medium text-gray-500 capitalize hover:text-highlight hover:bg-gray-100">
            {{ t('logout') }}
          </DisclosureButton>
        </div>
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
