<template>
  <div class="h-full w-full px-8">
    <MealsList
      id="mealsList"
      ref="mealsList"
      class="mb-10"
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
import { useComponentHeights } from '@/services/useComponentHeights';

const progress = useProgress().start();

const { loadShowParticipations } = getShowParticipations();
const { setMealsSummaryId, setMealsListId } = useComponentHeights();

onMounted(async () => {
  await loadShowParticipations();
  progress.finish();
  setMealsSummaryId('mealsOverview');
  setMealsListId('mealsList');
});
</script>