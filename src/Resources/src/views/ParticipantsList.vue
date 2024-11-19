<template>
  <div class="size-full px-4">
    <MealsList
      v-if="Object.keys(participationsState.meals ?? {}).length > 0"
      id="mealsList"
      ref="mealsList"
    />
    <ParticipationsTable
      v-if="Object.keys(participationsState.meals ?? {}).length > 0"
      class="mx-auto"
    />
    <NoParticipations
      v-if="Object.keys(participationsState.meals ?? {}).length === 0 && loadedState.loaded === true"
      :day="participationsState.day"
    />
    <MealOverview
      id="mealsOverview"
      ref="mealsOverview"
      class="mx-auto mt-6"
    />
  </div>
</template>

<script setup lang="ts">
import ParticipationsTable from '@/components/participations/ParticipationsTable.vue';
import MealOverview from '@/components/participations/MealOverview.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { onMounted, onUnmounted } from 'vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import MealsList from '@/components/participations/MealsList.vue';
import NoParticipations from '@/components/participations/NoParticipations.vue';
import { useComponentHeights } from '@/services/useComponentHeights';

const progress = useProgress().start();

const { participationsState, loadShowParticipations, activatePeriodicFetch, disablePeriodicFetch, loadedState } =
  getShowParticipations();
const { addWindowHeightListener, removeWindowHeightListener } = useComponentHeights();

onMounted(async () => {
  await loadShowParticipations();
  progress.finish();
  activatePeriodicFetch();
  addWindowHeightListener();
});

onUnmounted(() => {
  disablePeriodicFetch();
  removeWindowHeightListener();
});
</script>
