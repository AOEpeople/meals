<template>
  <BalanceHeader />

  <span class="font-bold tracking-[1px] text-note float-right text-primary-1 uppercase mr-[5%] xl:mr-0">
    {{ t('balance.old') }} {{ oldDateString }}:
    <span :class="[oldBalance >= 0 ? 'text-green' : 'text-red']">
      € {{ oldBalance.toFixed(2) }}
    </span>
  </span>

  <Table :labels="tableLabels" class="mt-10 mb-5 mx-[5%] xl:mx-0">
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
          {{ transaction.type === 'credit' ? '+ ' + parseInt(transaction.amount).toFixed(2) : '- ' + transaction.amount.toFixed(2) }} €
        </span>
      </td>
    </tr>
  </Table>

  <div class="text-right">
    <span class="contents font-bold tracking-[1px] text-note float-right text-primary-1 uppercase mr-[5%] xl:mr-0">
      {{ t('balance.current') }}:
      <span :class="[balance >= 0 ? 'text-green' : 'text-red']">
        € {{ balance }}
      </span>
    </span>
  </div>
</template>

<script setup>
import Table from '@/components/misc/Table.vue'
import BalanceDesc from "@/components/balance/BalanceDesc.vue";
import BalanceHeader from "@/components/balance/BalanceHeader.vue";

import { useTransactions } from '@/hooks/getTransactions';
import { useI18n } from "vue-i18n";

const { transactions } = await useTransactions();
const { t, locale } = useI18n();

let balance = 0;
let oldBalance = 0;

try {
  let balanceString = sessionStorage.getItem('balance');

  if(!balanceString) throw new Error('balance not set in session storage');
  if(!transactions) throw new Error('could not acquire transactions');

  balance = parseFloat(balanceString);
  oldBalance = balance - transactions.value.difference;

} catch (e) {
  console.log(e);
}
console.log(transactions.value.data)
let tableLabels = {
  en: ['Date', 'Description', 'Amount'],
  de: ['Datum', 'Beschreibung', 'Menge']
};

let dateOptions = { month: "short", day: "numeric", year: "numeric" };

let oldDate = new Date();
oldDate.setDate(oldDate.getDate() - 28);
const oldDateString = oldDate.toLocaleDateString(locale.value, dateOptions)
</script>
