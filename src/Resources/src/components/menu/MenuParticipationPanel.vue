<template>
  <div class="grid w-[300px] grid-rows-1 gap-2 overflow-hidden pb-2">
    <div class="flex flex-row rounded-t-lg bg-[#1c5298] px-1 py-2">
      <span class="grow self-center justify-self-center font-bold uppercase leading-4 tracking-[3px] text-white">
        Limit
      </span>
      <XCircleIcon
        class="h-8 w-8 cursor-pointer self-end text-white transition-transform hover:scale-[120%] hover:text-[#FAFAFA]"
        @click="close()"
      />
    </div>
    <MealParticipationInput
      v-for="meal in mealList"
      :key="meal.dishSlug"
      :meal="meal"
      class="px-2"
    />
  </div>
</template>

<script setup lang="ts">
import { MealDTO } from '@/interfaces/DayDTO';
import { Dictionary } from 'types/types';
import MealParticipationInput from './MealParticipationInput.vue';
import { computed } from 'vue';
import { XCircleIcon } from '@heroicons/vue/solid';

const props = defineProps<{
  meals: Dictionary<MealDTO[]>,
  close: () => void
}>();

const mealList = computed(() => {
  const keys = Object.keys(props.meals);
  return [...props.meals[keys[0]], ...props.meals[keys[1]]];
});
</script>