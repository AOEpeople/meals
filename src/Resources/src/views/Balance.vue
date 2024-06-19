<template>
  <div class="mx-[5%] xl:mx-auto">
    <BalanceHeader />
    <div class="mb-10 text-center sm:text-right">
      <span class="float-right contents text-note font-bold uppercase tracking-[1px] text-primary-1">
        {{ t('balance.old') }} {{ oldDateString }}:
        <span :class="[oldBalance >= 0 ? 'text-green' : 'text-red', 'whitespace-nowrap']">
          {{ locale === 'en' ? '€ ' + oldBalanceString : oldBalanceString + ' €' }}
        </span>
      </span>
    </div>

    <Table
      v-if="!transactions.isLoading && transactions.data.length !== 0"
      :labels="[t('balance.table.date'), t('balance.table.description'), t('balance.table.amount')]"
      class="mb-5 mt-10"
    >
      <tr
        v-for="(transaction, index) in transactions.data"
        :key="index"
        class="max-h-[62px] border-b-2 border-gray-200"
      >
        <td>
          <span class="text-[12px] xl:text-[18px]">
            {{
              new Date(transaction.date.date).toLocaleDateString(locale, {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
              })
            }}
          </span>
        </td>
        <td class="flex">
          <BalanceDesc :transaction="transaction" />
        </td>
        <td :class="[transaction.type === 'credit' ? 'text-green' : 'text-red', 'text-right']">
          <span class="whitespace-nowrap text-[14px] xl:text-[18px]">
            {{
              (transaction.type === 'credit' ? '+ ' : '- ') +
              (locale === 'en' ? transaction.amount.toFixed(2) : transaction.amount.toFixed(2).replace(/\./g, ','))
            }}
            €
          </span>
        </td>
      </tr>
    </Table>

    <div class="mb-10 text-right">
      <span class="float-right contents text-note font-bold uppercase tracking-[1px] text-primary-1">
        {{ t('balance.current') }}:
        <span :class="[balance >= 0 ? 'text-green' : 'text-red', 'whitespace-nowrap']">
          {{ locale === 'en' ? '€ ' + balanceString : balanceString + ' €' }}
        </span>
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import BalanceDesc from '@/components/balance/BalanceDesc.vue';
import BalanceHeader from '@/components/balance/BalanceHeader.vue';
import { useI18n } from 'vue-i18n';
import { useProgress } from '@marcoschulte/vue3-progress';
import { transactionStore } from '@/stores/transactionStore';
import { computed } from 'vue';
import { userDataStore } from '@/stores/userDataStore';

const progress = useProgress().start();

const { t, locale } = useI18n();
transactionStore.fillStore();

const transactions = computed(() => transactionStore.getState());
const balance = computed(() => userDataStore.getState().balance);
const balanceString = computed(() => userDataStore.balanceToLocalString(locale.value));
const oldBalance = computed(() => balance.value - transactions.value.difference);
const oldBalanceString = computed(() =>
  locale.value === 'en' ? oldBalance.value.toFixed(2) : oldBalance.value.toFixed(2).replace(/\./g, ',')
);

let oldDate = new Date();
oldDate.setDate(oldDate.getDate() - 28);
const oldDateString = computed(() =>
  oldDate.toLocaleDateString(locale.value, { month: 'long', day: 'numeric', year: 'numeric' })
);

progress.finish();
</script>
