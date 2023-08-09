<template>
  <div class="mx-[5%] xl:mx-auto">
    <CostsHeader
      v-model="filter"
    />
    <CostsTable
      v-if="loaded === true"
      :filter="filter"
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

onMounted(async () => {
  const progress = useProgress().start();

  await fetchCosts();
  loaded.value = true;

  progress.finish();
});
</script>