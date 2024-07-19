<template v-if="loaded && FinancesState.finances !== undefined">
  <div
    v-for="(finance, index) in FinancesState.finances"
    :key="index"
    class="mx-[5%] mt-6 xl:mx-auto"
  >
    <FinanceHeader
      :date-range="finance.heading"
      :show-controls="Number(index) === 0"
      @date-changed="handleDateChange"
      @generate-pdf="pdfCreator?.downloadPdf()"
    />
    <FinanceTable :transactions="finance.transactions" />
  </div>
  <PdfCreator
    v-if="loaded === true"
    ref="pdfCreator"
    filename="finanzen"
    :content-hidden="true"
  >
    <FinancePdfTemplate
      v-if="loaded && FinancesState.finances !== undefined && FinancesState.finances.length > 0"
      :finances="FinancesState.finances[0]"
      class="mt-8"
    />
  </PdfCreator>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import FinanceTable from '@/components/finance/FinanceTable.vue';
import { useFinances } from '@/stores/financesStore';
import FinanceHeader from '@/components/finance/FinanceHeader.vue';
import FinancePdfTemplate from '@/components/finance/FinancePdfTemplate.vue';
import PdfCreator from '@/components/pdfCreator/PdfCreator.vue';

const { fetchFinances, FinancesState } = useFinances();
const loaded = ref(false);

const pdfCreator = ref(null);

onMounted(async () => {
  await fetchFinances();
  loaded.value = true;
});

const handleDateChange = (modelData: Date[]) => {
  fetchFinances(modelData);
};
</script>
