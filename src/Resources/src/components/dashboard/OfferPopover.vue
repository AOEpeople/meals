<template>
  <div class="relative flex cursor-default flex-col">
    <div
      class="absolute -right-[10px] -top-[83px] z-10 inline-block h-auto w-[120px] rounded-[5px] bg-black/75 p-1 text-center leading-[14px]"
    >
      <span class="text-[11px] text-white">{{ t('dashboard.popover') }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
