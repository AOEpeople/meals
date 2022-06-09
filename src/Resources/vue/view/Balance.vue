<template>
  <div class="items-center text-center xl:grid xl:grid-cols-2">
    <div class="xl:justify-self-start">
      <h1 class="m-0">Transactions</h1>
    </div>
    <div class="xl:justify-self-end">
      <Popover>
        <template #button="{ open }">
          <button class="btn-secondary">+ Add Funds</button>
        </template>
        <template #panel>
          <TransactionPanel />
        </template>
      </Popover>
    </div>
  </div>
  <a class="font-bold tracking-[1px] text-note float-right text-primary-1 uppercase">
    balance on {{ oldDateString }}:
    <a :class="[oldBalance >= 0 ? 'text-green' : 'text-red']">
      € {{ oldBalance.toFixed(2) }}
    </a>
  </a>

  <Table :labels="tableLabels">
    <tr v-for="(transaction, index) in transactions.data" :key="index">
      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
        {{ transaction.date }}
      </td>
      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
        {{ transaction.type === 'transaction' ? 'Cash' : transaction.description }}
      </td>
      <td :class="[transaction.type === 'transaction' ? 'text-green' : 'text-red', 'whitespace-nowrap py-4 px-3 text-sm']">
        {{ transaction.type === 'transaction' ? '+ ' + parseInt(transaction.amount).toFixed(2) : '- ' + transaction.amount.toFixed(2) }} €
      </td>
    </tr>
  </Table>

  <a class="font-bold tracking-[1px] text-note float-right text-primary-1">
    CURRENT BALANCE:
    <a :class="[balance >= 0 ? 'text-green' : 'text-red']">
      € {{ balance }}
    </a>
  </a>
</template>

<script>
import Table from '@/components/Table.vue'
import useTransactions from '@/hooks/Transactions';
import {defineComponent} from "vue";
import Popover from "../components/Popover.vue";
import TransactionPanel from "../components/TransactionPanel.vue";
export default defineComponent({
  name: "Balance",
  components: {
    TransactionPanel,
    Popover,
    Table,
  },
  data() {
    return {
      tableLabels: ['Date', 'Description', 'Amount'],
    }
  },
  async setup() {
    const { transactions } = await useTransactions();
    let oldDate = new Date();
    oldDate.setDate(oldDate.getDate() - 28);
    console.log(oldDate)
    const oldDateString = oldDate.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    })

    let balance = parseFloat(sessionStorage.getItem('balance'));
    let oldBalance = balance;
    if(balance != null) {
      oldBalance = parseFloat(balance - transactions.value?.difference);
    }

    console.log([transactions, balance, transactions.value?.difference, oldBalance])
    return { transactions, balance, oldBalance, oldDateString };
  }
});
</script>
