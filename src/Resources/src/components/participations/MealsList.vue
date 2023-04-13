<template>
  <th
    v-for="(meal, index) in mealsWithVariations"
    :key="index"
    class="h-full w-full border-2 border-solid border-black"
  >
    <Meal
      :meal="meal"
    />
  </th>
</template>


<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import Meal from './Meal.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { IMealWithVariations } from '@/api/getShowParticipations';

const { loadShowParticipations, loadedState, getMealsWithVariations } = getShowParticipations();

// const mealsWithVariations = ref<IMealWithVariations[]>([]);

const mealsWithVariations = computed(() => {
  if(loadedState.loaded && loadedState.error === "") {
    return getMealsWithVariations();
  } else {
    return [];
  }
});

// onMounted(async () => {
//   await loadShowParticipations();
//   if(loadedState.loaded && loadedState.error === "") {
//     mealsWithVariations.value = getMealsWithVariations();
//   }
// });

</script>