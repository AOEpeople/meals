<template v-if="loaded && FinancesState.finances !== null">
  <div
    v-for="(finance, index) in FinancesState.finances"
    :key="index"
    class="mx-[5%] my-6 xl:mx-auto"
  >
    <FinanceHeader
      :date-range="finance.heading"
      :show-controls="index===0"
      @date-changed="handleDateChange"
    />
    <FinanceTable :transactions="finance.transactions" />
  </div>
</template>

<script setup lang="ts">
import {onMounted, ref} from 'vue';
import FinanceTable from '@/components/finance/FinanceTable.vue';
import {useFinances} from '@/stores/financesStore';
import FinanceHeader from '@/components/finance/FinanceHeader.vue';

const {fetchFinances, FinancesState} = useFinances();
const loaded = ref(false);

onMounted(async () => {
  await fetchFinances();
  loaded.value = true;
})

const handleDateChange = (modelData: Date[]) => {
  fetchFinances(modelData);
}
</script>
