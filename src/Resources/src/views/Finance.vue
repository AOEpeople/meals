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
      @generate-pdf="download()"
    />
    <FinanceTable :transactions="finance.transactions" />
  </div>
  <div class="m-4 p-4">
    <Vue3Html2pdf
      ref="html2pdf"
      :html-to-pdf-options="{ filename: 'result.pdf', image: { type: 'png' }, margin: 10, jsPDF: { unit: 'mm' } }"
      :pdf-quality="2"
      pdf-format="a4"
      pdf-orientation="portrait"
      pdf-content-width="700px"
      :manual-pagination="true"
    >
      <template #pdf-content>
        <FinancePdfTemplate
          v-if="loaded && FinancesState.finances !== undefined && FinancesState.finances.length > 0"
          :finances="FinancesState.finances[0]"
        />
      </template>
    </Vue3Html2pdf>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import FinanceTable from '@/components/finance/FinanceTable.vue';
import { useFinances } from '@/stores/financesStore';
import FinanceHeader from '@/components/finance/FinanceHeader.vue';
import Vue3Html2pdf from 'vue3-html2pdf';
import FinancePdfTemplate from '@/components/finance/FinancePdfTemplate.vue';

const { fetchFinances, FinancesState } = useFinances();
const loaded = ref(false);

const html2pdf = ref(null);

onMounted(async () => {
  await fetchFinances();
  loaded.value = true;
});

const handleDateChange = (modelData: Date[]) => {
  fetchFinances(modelData);
};

function download() {
  if (html2pdf.value) {
    html2pdf.value.generatePdf();
  } else {
    console.log('Html2Pdf not found');
  }
}
</script>
