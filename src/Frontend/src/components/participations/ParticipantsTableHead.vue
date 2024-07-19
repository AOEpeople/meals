<template>
  <thead
    ref="tableHead"
    class="z-10 shrink-0 grow-0 basis-auto rounded-t-[18px] bg-white shadow-[0_15px_35px_0_#5B788F21]"
  >
    <tr class="table w-full table-fixed align-top">
      <th
        class="h-full w-2/3 px-2 py-4 text-primary"
        data-test="meal-head-th"
      >
        Name
      </th>
      <th
        v-for="meal in mealsWithVariations"
        :key="meal.mealId"
        class="h-full border-l px-2 py-4 text-primary"
        :class="meal.title.en === 'Combined Dish' ? 'w-1/3' : 'w-full'"
        data-test="meal-head-th"
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
  if (loadedState.loaded === true) {
    return getMealsWithVariations();
  } else {
    return [];
  }
});
</script>
