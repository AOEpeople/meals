<template>
  <th
    v-for="(meal, index) in mealsWithVariations"
    :key="index"
    class="h-full border-2"
    :class="meal.title.en === 'Combined Dish' ? 'w-1/2' : 'w-full'"
  >
    <Meal
      :meal="meal"
    />
  </th>
</template>


<script setup lang="ts">
import { computed } from 'vue';
import Meal from './Meal.vue';
import { getShowParticipations } from '@/api/getShowParticipations';

const { loadedState, getMealsWithVariations } = getShowParticipations();

const mealsWithVariations = computed(() => {
  if(loadedState.loaded && loadedState.error === "") {
    return getMealsWithVariations();
  } else {
    return [];
  }
});
</script>