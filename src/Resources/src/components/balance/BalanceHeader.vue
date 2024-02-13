<template>
  <div class="items-center text-center xl:my-[42px] xl:grid xl:grid-cols-2">
    <div class="xl:justify-self-start">
      <h2 class="m-0">
        {{ t('balance.transactions') }}
      </h2>
    </div>
    <div class="xl:justify-self-end">
      <Popover
        :translate-x-min="'-18%'"
        class="m-auto w-fit"
      >
        <template #button="{ open }">
          <button
            class="hover:bg-highlight-2 btn-highlight-shadow mx-2 mb-6 mt-4 h-9 items-center rounded-btn bg-highlight px-[34px] text-center text-btn font-bold text-white shadow-btn drop-shadow-btn transition-all duration-300 ease-out active:translate-y-0.5 active:shadow-btn-active"
          >
            <span class="align-middle leading-[10px]">+ {{ t('balance.add') }}</span>
          </button>
        </template>
        <template #panel="{ close }">
          <TransactionPanel @closePanel="close()" />
        </template>
      </Popover>
    </div>
  </div>
</template>

<script setup lang="ts">
import Popover from '@/components/misc/Popover.vue';
import TransactionPanel from '@/components/balance/TransactionPanel.vue';

import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>
