<template>
  <div class="xl:mx-auto mx-[5%]">
    <BalanceHeader />
    <div class="text-right mb-[2.5rem]">
      <span class="contents font-bold tracking-[1px] text-note float-right text-primary-1 uppercase">
        {{ t('balance.old') }} {{ oldDateString }}:
        <span :class="[oldBalance >= 0 ? 'text-green' : 'text-red', 'whitespace-nowrap']">
          € {{ oldBalanceString }}
        </span>
      </span>
    </div>

    <Table
      v-if="!transactions.isLoading"
      :labels="tableLabels"
      class="mt-10 mb-5"
    >
      <tr
        v-for="(transaction, index) in transactions.data"
        :key="index"
        class="max-h-[62px] border-b-2 border-gray-200"
      >
        <td>
          <span class="text-[12px] xl:text-[18px]">
            {{ new Date(transaction.date.date).toLocaleDateString(locale, dateOptions) }}
          </span>
        </td>
        <td class="flex">
          <BalanceDesc :transaction="transaction" />
        </td>
        <td :class="[transaction.type === 'credit' ? 'text-green' : 'text-red', 'text-right']">
          <span class="whitespace-nowrap">
            {{ (transaction.type === 'credit' ? '+ ' : '- ') +
              (locale === 'en' ? transaction.amount.toFixed(2) : transaction.amount.toFixed(2).replace(/\./g, ',')) }} €
          </span>
        </td>
      </tr>
    </Table>

    <div class="text-right mb-[2.5rem]">
      <span class="contents font-bold tracking-[1px] text-note float-right text-primary-1 uppercase">
        {{ t('balance.current') }}:
        <span :class="[balance >= 0 ? 'text-green' : 'text-red', 'whitespace-nowrap']">
          € {{ balanceString }}
        </span>
      </span>
    </div>
  </div>
</template>

<script setup>
import Table from '@/components/misc/Table.vue'
import BalanceDesc from "@/components/balance/BalanceDesc.vue"
import BalanceHeader from "@/components/balance/BalanceHeader.vue"

import { useI18n } from "vue-i18n"
import {balanceStore} from "@/store/balanceStore"
import {useProgress} from '@marcoschulte/vue3-progress'
import {transactionStore} from "@/store/transactionStore"
import {computed} from "vue"

const progress = useProgress().start()

const { t, locale } = useI18n()
transactionStore.fillStore()

let transactions = computed(() => transactionStore.getState());
let balance = computed(() => balanceStore.getState().amount);
let balanceString = computed(() => balanceStore.toLocalString());
let oldBalance = computed(() => balance.value - transactions.value.difference);
let oldBalanceString = computed(() =>
    locale.value === 'en'
        ? oldBalance.value.toFixed(2)
        : oldBalance.value.toFixed(2).replace(/\./g, ',')
)

let tableLabels = {
  en: ['Date', 'Description', 'Amount'],
  de: ['Datum', 'Beschreibung', 'Menge']
};

let dateOptions = { month: "short", day: "numeric", year: "numeric" }

let oldDate = new Date()
oldDate.setDate(oldDate.getDate() - 28)
const oldDateString = oldDate.toLocaleDateString(locale.value, dateOptions)

progress.finish()
</script>
