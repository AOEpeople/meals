<template>
  <thead
    ref="tableHead"
    class="shrink-0 grow-0 basis-auto bg-white"
  >
    <tr class="table w-full table-fixed align-top">
      <th class="h-full w-1/2 border-2 p-2">
        Name
      </th>
      <th
        v-for="meal in mealsWithVariations"
        :key="meal.mealId"
        class="h-full border-2 p-2"
        :class="meal.title.en === 'Combined Dish' ? 'w-1/2' : 'w-full'"
      >
        <MealHead :meal="meal" />
      </th>
    </tr>
  </thead>
</template>

<script setup lang="ts">
import { getShowParticipations } from '@/api/getShowParticipations';
import { computed } from 'vue';
import MealHead from './MealHead.vue';

const { getMealsWithVariations, loadedState } = getShowParticipations();

const mealsWithVariations = computed(() => {
  if(loadedState.loaded && loadedState.error === "") {
    return getMealsWithVariations();
  } else {
    return [];
  }
});
</script>