<template>
  <div class="relative flex">
    <div class="absolute z-10 inline-block h-auto w-[120px] rounded-[5px] bg-black/75 p-1 text-center leading-[14px]">
      <span class="text-[11px] text-white">{{ t('dashboard.popover') }}</span>
    </div>
    <div class="absolute h-0 w-0 border-x-[10px] border-t-[10px] border-black/75 border-x-transparent" />
  </div>
</template>
<script setup>
import {useI18n} from "vue-i18n";

const { t } = useI18n()
</script>
