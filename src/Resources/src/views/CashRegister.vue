<template>
  <div class="mx-[5%] xl:mx-auto">
    <CashRegisterTable
      v-if="loaded === true && TransactionState.transactions && TransactionState.transactions.usersLastMonth !== null"
      :transactions="TransactionState.transactions.usersLastMonth"
      :date-range="TransactionState.transactions.lastMonth"
      class="mb-8"
    />
    <CashRegisterTable
      v-if="loaded === true && TransactionState.transactions && TransactionState.transactions.usersThisMonth !== null"
      :transactions="TransactionState.transactions.usersThisMonth"
      :date-range="TransactionState.transactions.thisMonth"
    />
    <LoadingSpinner :loaded="loaded" />
  </div>
</template>

<script setup lang="ts">
import CashRegisterTable from '@/components/cashRegister/CashRegisterTable.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import { useAccounting } from '@/stores/accountingStore';
import { onMounted, ref } from 'vue';

const { TransactionState, fetchTransactionHistory } = useAccounting();
const loaded = ref(false);

onMounted(async () => {
  await fetchTransactionHistory();
  loaded.value = true;
});
</script>
