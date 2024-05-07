<template>
  <Vue3Html2pdf
    v-if="loaded === true"
    ref="html2pdf"
    :html-to-pdf-options="{ filename: 'kosten.pdf', image: { type: 'jpeg', quality: 0.98 }, margin: 10, jsPDF: { unit: 'mm' }, html2canvas: { dpi: 192, letterRendering: true } }"
    :pdf-quality="2"
    pdf-format="a4"
    pdf-orientation="portrait"
    pdf-content-width="700px"
    :manual-pagination="true"
  >
    <template #pdf-content>
      <SimpleCostsTable ref="costsTableToPrint" />
    </template>
  </Vue3Html2pdf>
  <div class="mx-[5%] xl:mx-auto">
    <CostsHeader
      v-model="filter"
      :show-hidden="showHidden"
      :print-active="loaded"
      class="mb-4"
      @change:show-hidden="(value) => (showHidden = value)"
      @print-costs="() => generatePdf()"
    />
    <CostsTable
      v-if="loaded === true"
      :filter="filter"
      :showHidden="showHidden"
    />
    <LoadingSpinner :loaded="loaded" />
  </div>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted, ref } from 'vue';
import { useCosts } from '@/stores/costsStore';
import CostsTable from '@/components/costs/CostsTable.vue';
import CostsHeader from '@/components/costs/CostsHeader.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import Vue3Html2pdf from 'vue3-html2pdf';
import SimpleCostsTable from '@/components/costs/SimpleCostsTable.vue';

const { fetchCosts } = useCosts();
const loaded = ref(false);
const filter = ref('');
const showHidden = ref(false);
const html2pdf = ref(null);
const costsTableToPrint = ref(null);

onMounted(async () => {
  const progress = useProgress().start();

  await fetchCosts();
  loaded.value = true;

  progress.finish();
});

function generatePdf() {
  if (html2pdf.value) {
    html2pdf.value.generatePdf();
  } else {
    console.log('Html2Pdf not found');
  }
}
</script>
