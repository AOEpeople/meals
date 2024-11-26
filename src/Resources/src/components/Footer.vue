<template>
  <footer class="mt-8 bg-[rgb(225,232,238)] print:hidden">
    <div class="mx-auto grid max-w-screen-aoe xl:grid-cols-2">
      <div class="items-center py-4">
        <img
          class="mx-auto h-10 xl:mx-0"
          src="../../images/aoe-logo.svg"
          alt="AOE Logo"
        />
      </div>
      <div
        id="language"
        class="hidden w-fit cursor-pointer self-center justify-self-end text-right xl:inline-block"
        @click="changeLocale"
      >
        <Icons
          icon="flag"
          box="0 0 26 26"
          class="inline-block size-[26px] fill-primary align-top"
        />
        <span class="self-center align-top text-[14px] leading-[20px] text-primary">
          {{ t('changeLanguage') }}
        </span>
      </div>
    </div>
    <div class="w-full bg-[rgb(244,247,249)] p-4 text-center">
      <span class="text-[14px] font-normal leading-[20px] text-[#A1A1B0]">
        © {{ year }} AOE. {{ t('copyright') }}
      </span>
    </div>
  </footer>
</template>

<script setup lang="ts">
import Icons from '@/components/misc/Icons.vue';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

function changeLocale() {
  locale.value = locale.value.substring(0, 2) === 'en' ? 'de' : 'en';
  localStorage.Lang = locale.value;
}

onMounted(() => {
  if (typeof localStorage.Lang !== 'undefined' && localStorage.Lang !== null) locale.value = localStorage.Lang;
});

const year = ref(new Date().getFullYear());
</script>

<style scoped>
#language:hover svg {
  @apply fill-secondary;
}
#language:hover span {
  @apply text-secondary;
}
</style>
