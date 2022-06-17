<template>
  <div class="items-center text-center xl:grid xl:grid-cols-2">
    <div class="xl:justify-self-start">
      <h1 class="m-0">{{ t('balance.transactions') }}</h1>
    </div>
    <div class="xl:justify-self-end">
      <Popover>
        <template #button="{ open }">
          <button class="btn-secondary">+ {{ t('balance.add') }}</button>
        </template>
        <template #panel="{ close }">
          <TransactionPanel @closePanel="close()" />
        </template>
      </Popover>
    </div>
  </div>
</template>

<script setup>
import Popover from "@/components/misc/Popover.vue";
import TransactionPanel from "@/components/balance/TransactionPanel.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();
</script>

<style scoped>

</style>