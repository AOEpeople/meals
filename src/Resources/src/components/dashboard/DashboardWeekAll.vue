<template>
  <div class="inline-flex divide-x-2 print:block print:h-screen">
    <div
      v-for="(weekID, index) in weekIdsSortedByDate"
      :key="index"
      class="w-[585px] flex-initial print:mx-auto print:w-full print:last:hidden"
    >
      <WeekComp
        :weekID="weekID"
        :index="index"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { type Dictionary } from '@/types/types';
import WeekComp from './Week.vue';
import { type Week } from '@/api/getDashboardData';
import { computed } from 'vue';
import {useTransactionData} from '@/api/getTransactionData';
import useFlashMessage from '@/services/useFlashMessage';
import {FlashMessageType} from '@/enums/FlashMessage';

const props = defineProps<{
  weeks: Dictionary<Week>;
}>();

const weekIdsSortedByDate = computed(() =>
  Object.keys(props.weeks).sort((a, b) => {
    const startDateA = new Date(props.weeks[a].startDate.date);
    const startDateB = new Date(props.weeks[b].startDate.date);

    if (startDateA > startDateB) {
      return 1;
    } else {
      return -1;
    }
  })
);

const transactionData = await useTransactionData();
const balanceDifference = transactionData.transactions.value?.difference ?? 0.0;
if ((balanceDifference <= import.meta.env.VITE_ACCOUNT_ORDER_LOCKED_BALANCE_LIMIT)) {
  useFlashMessage().clearMessages();
  useFlashMessage().sendFlashMessage({
    type: FlashMessageType.ERROR,
    message: 'balanceBelowBalanceLimit',
    hasLifetime: false
  });
}
</script>
