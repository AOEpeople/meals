<template>
  <router-link
    :to="'/cash-register'"
    class="flex flex-row items-center gap-2 text-[#173D7A] hover:text-highlight"
  >
    <CurrencyEuroIcon class="size-6" />
    <span>{{ t('costs.cashRegister') }}</span>
  </router-link>
</template>

<script setup lang="ts">
import { CurrencyEuroIcon } from '@heroicons/vue/outline';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
