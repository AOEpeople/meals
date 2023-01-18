<template>
  <div class="relative flex">
    <div class="absolute bottom-[46px] z-10 h-auto w-auto rounded-[5px] bg-black/90 p-1 text-center">
      <span class="whitespace-pre text-[11px] leading-[14px] text-white">{{ t('dashboard.popover') }}</span>
    </div>
    <div class="absolute left-[40px] h-0 w-0 border-x-[10px] border-t-[10px] border-black/90 border-x-transparent" />
  </div>
</template>
<script setup>
import {useI18n} from "vue-i18n";

const { t } = useI18n()
</script>
