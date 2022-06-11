<template>
  <footer class="bg-[rgb(225,232,238)]">
    <div class="grid mx-auto max-w-screen-aoe xl:grid-cols-2">
      <div class="items-center py-4">
        <img class="mx-auto h-10 xl:mx-0" src="../../images/aoe-logo.svg" alt="AOE Logo" />
      </div>
      <div class="hidden self-center text-right xl:inline-block cursor-pointer" @click="changeLocale">
        <a id="language">
          <Icons icon="flag" box="0 0 26 26" class="inline-block w-[26px] h-[26px] align-top fill-primary" />
          <span class="self-center text-primary text-[14px] leading-[20px] align-top">
            {{ t('changeLanguage') }}
          </span>
        </a>
      </div>
    </div>
    <div class="bg-[rgb(244,247,249)] w-full py-4 px-4 text-center">
      <span class="text-[#A1A1B0] font-normal text-[14px] leading-[20px]"> © 2022 AOE. {{ t('copyright') }} </span>
    </div>
    <router-link to="/guest">guest</router-link>
  </footer>
</template>

<script setup>
import Icons from "@/components/misc/Icons.vue"
import { useI18n } from "vue-i18n";

const { t, locale } = useI18n();

const changeLocale = () => {
  locale.value = locale.value.substring(0, 2) === 'en' ? 'de' : 'en';
}
</script>
<style scoped>
#language:hover svg {
  @apply fill-secondary;
}
#language:hover span {
  @apply text-secondary;
}
</style>