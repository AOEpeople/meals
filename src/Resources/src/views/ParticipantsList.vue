<template>
  <div class="h-full w-full px-4">
    <MealsList
      id="mealsList"
      ref="mealsList"
    />
    <ParticipationsTable class="mx-auto" />
    <MealOverview
      id="mealsOverview"
      ref="mealsOverview"
      class="mx-auto"
    />
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