<template>
  <router-link
    :to="'/costs'"
    class="flex flex-row items-center gap-2"
  >
    <CurrencyEuroIcon class="h-6 w-6" />
    <span>{{ t('costs.cashRegister') }}</span>
  </router-link>
</template>

<script setup lang="ts">
import { CurrencyEuroIcon } from '@heroicons/vue/outline';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>