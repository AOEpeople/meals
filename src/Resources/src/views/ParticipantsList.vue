<template>
  <div class="h-full w-full">
    <MealsList class="mb-10" />
    <ParticipationsTable class="max-w-screen-aoe mx-auto" />
    <MealOverview class="max-w-screen-aoe mx-auto bg-white" />
  </div>
</template>


<script setup lang="ts">
import ParticipationsTable from '@/components/participations/ParticipationsTable.vue';
import MealOverview from '@/components/participations/MealOverview.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { onMounted } from 'vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import MealsList from '@/components/participations/MealsList.vue';

const progress = useProgress().start();

const { loadShowParticipations } = getShowParticipations();

onMounted(async () => {
  await loadShowParticipations();
  progress.finish();
});


</script>