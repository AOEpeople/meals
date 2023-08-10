<template>
  <div class="mx-[5%] xl:mx-auto">
    <CostsHeader
      v-model="filter"
      :show-hidden="showHidden"
      class="mb-4"
      @change:show-hidden="(value) => showHidden = value"
    />
    <CostsTable
      v-if="loaded === true"
      :filter="filter"
      :showHidden="showHidden"
    />
  </div>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted, ref } from 'vue';
import { useCosts } from '@/stores/costsStore';
import CostsTable from '@/components/costs/CostsTable.vue';
import CostsHeader from '@/components/costs/CostsHeader.vue';

const { fetchCosts } = useCosts();
const loaded = ref(false);
const filter = ref('');
const showHidden = ref(false);

onMounted(async () => {
  const progress = useProgress().start();

  await fetchCosts();
  loaded.value = true;

  progress.finish();
});
</script>