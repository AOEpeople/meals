<template>
  <div class="mx-[5%] xl:mx-auto">
    <CostsHeader
      v-model="filter"
      :show-hidden="showHidden"
      :print-active="loaded"
      class="mb-4"
      @change:show-hidden="(value) => (showHidden = value)"
      @print-costs="pdfCreator?.downloadPdf()"
    />
    <CostsTable
      v-if="loaded === true"
      :filter="filter"
      :showHidden="showHidden"
      data-cy="costsTable"
    />
    <LoadingSpinner :loaded="loaded" />
  </div>
  <PdfCreator
    v-if="loaded === true"
    ref="pdfCreator"
    filename="kosten"
    :content-hidden="true"
  >
    <SimpleCostsTable
      ref="costsTableToPrint"
      class="px-8 pt-8"
    />
  </PdfCreator>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted, ref } from 'vue';
import { useCosts } from '@/stores/costsStore';
import CostsTable from '@/components/costs/CostsTable.vue';
import CostsHeader from '@/components/costs/CostsHeader.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import PdfCreator from '@/components/pdfCreator/PdfCreator.vue';
import SimpleCostsTable from '@/components/costs/SimpleCostsTable.vue';

const { fetchCosts } = useCosts();
const loaded = ref(false);
const filter = ref('');
const showHidden = ref(false);
const pdfCreator = ref();

onMounted(async () => {
  const progress = useProgress().start();

  await fetchCosts();
  loaded.value = true;

  progress.finish();
});
</script>
