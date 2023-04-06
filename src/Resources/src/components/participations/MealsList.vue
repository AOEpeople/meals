<template>
  <div class="flex flex-row gap-1">
    <Meal
      v-for="(meal, index) in mealsWithVariations"
      :key="index"
      :meal="meal"
      class="flex-1"
    />
  </div>
</template>


<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Meal from './Meal.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { IMealWithVariations } from '@/api/getShowParticipations';

const { loadShowParticipations, loadedState, getMealsWithVariations } = getShowParticipations();

const mealsWithVariations = ref<IMealWithVariations[]>([]);

onMounted(async () => {
  await loadShowParticipations();
  if(loadedState.loaded && loadedState.error === "") {
    mealsWithVariations.value = getMealsWithVariations();
  }
});

</script>