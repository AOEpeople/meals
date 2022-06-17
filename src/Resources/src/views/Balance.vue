<template>
  <BalanceHeader />

  <span class="font-bold tracking-[1px] text-note float-right text-primary-1 uppercase mr-[5%] xl:mr-0">
    {{ t('balance.old') }} {{ oldDateString }}:
    <span :class="[oldBalance >= 0 ? 'text-green' : 'text-red']">
      € {{ oldBalance.toFixed(2) }}
    </span>
  </span>

  <span v-if="transactions.isLoading">TEST</span>
  <Table v-if="!transactions.isLoading" :labels="tableLabels" class="mt-10 mb-5 mx-[5%] xl:mx-0">
    <tr v-for="(transaction, index) in transactions.data" :key="index" class="max-h-[62px]">
      <td>
        <span>
          {{ new Date(transaction.date.date).toLocaleDateString(locale, dateOptions) }}
        </span>
      </td>
      <td class="flex">
        <BalanceDesc :transaction="transaction" />
      </td>
      <td :class="[transaction.type === 'credit' ? 'text-green' : 'text-red', 'text-right']">
        <span>
          {{ (transaction.type === 'credit' ? '+ '  : '- ') + transaction.amount.toFixed(2) }} €
        </span>
      </td>
    </tr>
  </Table>

  <div class="text-right">
    <span class="contents font-bold tracking-[1px] text-note float-right text-primary-1 uppercase mr-[5%] xl:mr-0">
      {{ t('balance.current') }}:
      <span :class="[balance >= 0 ? 'text-green' : 'text-red']">
        € {{ balance.toFixed(2) }}
      </span>
    </span>
  </div>
</template>

<script setup>
import Table from '@/components/misc/Table.vue'
import BalanceDesc from "@/components/balance/BalanceDesc.vue";
import BalanceHeader from "@/components/balance/BalanceHeader.vue";

import { useI18n } from "vue-i18n";
import {balanceStore} from "@/store/balanceStore";
import {transactionStore} from "@/store/transactionStore";
import {computed} from "vue";

const { t, locale } = useI18n();
transactionStore.fillStore();

let transactions = computed(() => transactionStore.getState());
let balance = computed(() => balanceStore.getState().amount);
let oldBalance = computed(() => balance.value - transactions.value.difference);

let tableLabels = {
  en: ['Date', 'Description', 'Amount'],
  de: ['Datum', 'Beschreibung', 'Menge']
};

let dateOptions = { month: "short", day: "numeric", year: "numeric" };

let oldDate = new Date();
oldDate.setDate(oldDate.getDate() - 28);
const oldDateString = oldDate.toLocaleDateString(locale.value, dateOptions)
</script>
