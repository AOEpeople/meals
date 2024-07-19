<template>
  <Table
    :labels="[
      t('finance.table.date'),
      t('finance.table.name'),
      t('finance.table.amount'),
      t('finance.table.dailyClosing')
    ]"
    :print="print"
    :add-styles="'first:sticky first:left-0 first:bg-[#f4f7f9] last:pl-4 last:truncate'"
    :overflow-table="true"
  >
    <div
      v-for="([date, dateTransactions], index) in Object.entries(transactions)"
      :key="date"
      class="contents"
    >
      <tr
        v-for="(transaction, i) in dateTransactions"
        :key="i"
        class="border-b-2 border-gray-200 text-[12px] xl:text-[18px]"
      >
        <td
          class="w-50% p-2"
          colspan="1"
        >
          {{ (transaction as Transaction).date }}
        </td>
        <td
          class="w-50% p-2"
          colspan="1"
        >
          {{ (transaction as Transaction).name }} {{ (transaction as Transaction).firstName }}
        </td>
        <td
          class="w-50% p-2"
          colspan="1"
        >
          {{
            new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: 'EUR'
            }).format((transaction as Transaction).amount)
          }}
        </td>
      </tr>
      <tr
        class="text-[12px] xl:text-[18px]"
        :class="[index !== Object.keys(transactions).length - 1 ? 'border-b-2 border-gray-200' : '']"
      >
        <td
          class="py-2 text-right"
          colspan="4"
        >
          {{
            new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: 'EUR'
            }).format(getTotalAmount(dateTransactions as Transaction[]))
          }}
        </td>
      </tr>
    </div>
  </Table>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import { useI18n } from 'vue-i18n';
import { Transaction } from '@/stores/financesStore';
import { Dictionary } from '../../../types/types';

const { t, locale } = useI18n();

withDefaults(
  defineProps<{
    transactions: Dictionary<Transaction[]>;
    print?: boolean;
  }>(),
  {
    print: false
  }
);

function getTotalAmount(transactions: Transaction[]) {
  return Object.values(transactions)
    .map((transaction) => transaction.amount)
    .reduce((total, transaction) => total + transaction, 0);
}
</script>
