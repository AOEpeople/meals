<template>
  <div class="flex h-fit flex-row gap-4">
    <Meal
      v-for="(meal, index) in mealsWithVariations"
      :key="index"
      class="flex-1 border-2"
      :meal="meal"
    />
  </div>
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