<template>
  <div
    id="mealsList"
    ref="mealsList"
    class="my-6 grid h-fit grid-flow-col auto-cols-[1fr] gap-4 p-0"
  >
    <Meal
      v-for="meal in mealsWithVariations"
      :key="meal.mealId"
      :meal="meal"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUpdated, ref, watch } from 'vue';
import Meal from './Meal.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { useComponentHeights } from '@/services/useComponentHeights';

const { loadedState, getMealsWithVariations } = getShowParticipations();
const { setMealListHight, windowWidth } = useComponentHeights();

const mealsList = ref<HTMLDivElement | null>(null);

const mealsWithVariations = computed(() => {
  if (loadedState.loaded === true) {
    return getMealsWithVariations();
  } else {
    return [];
  }
});

watch(windowWidth, () => {
  if (mealsList.value !== null && mealsList.value !== undefined) {
    setMealListHight(mealsList.value.offsetHeight, 'mealsList');
  }
});

onMounted(() => {
  if (mealsList.value !== null && mealsList.value !== undefined) {
    setMealListHight(mealsList.value.offsetHeight, 'mealsList');
  }
});

onUpdated(() => {
  if (mealsList.value !== null && mealsList.value !== undefined) {
    setMealListHight(mealsList.value.offsetHeight, 'mealsList');
  }
});
</script>
