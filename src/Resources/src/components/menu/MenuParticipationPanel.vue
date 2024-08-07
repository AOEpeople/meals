<template>
  <div class="grid w-[300px] grid-rows-1 gap-2 overflow-hidden pb-2">
    <div class="flex max-w-[300px] flex-row rounded-t-lg bg-primary-2 px-1 py-2">
      <span class="grow self-center justify-self-center font-bold uppercase leading-4 tracking-[3px] text-white">
        Limit
      </span>
      <XCircleIcon
        class="size-8 cursor-pointer self-end text-white transition-transform hover:scale-[120%] hover:text-[#FAFAFA]"
        @click="emit('closePanel')"
      />
    </div>
    <MealParticipationInput
      v-for="meal in mealList"
      :key="meal.dishSlug ?? ''"
      :meal="meal"
      class="max-w-[300px] px-2"
    />
    <span v-if="mealList.length < 1">
      {{ t('menu.noMeals') }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { type MealDTO } from '@/interfaces/DayDTO';
import { type Dictionary } from '@/types/types';
import MealParticipationInput from './MealParticipationInput.vue';
import { computed } from 'vue';
import { XCircleIcon } from '@heroicons/vue/solid';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
  meals: Dictionary<MealDTO[]>;
}>();

const emit = defineEmits(['closePanel']);

const mealList = computed(() => {
  const keys = Object.keys(props.meals);
  removeCombinedMealKey(keys);
  const returnMealDTOs: MealDTO[] = [];
  keys.forEach((key) => {
    if (props.meals[key].length > 0) {
      returnMealDTOs.push(...props.meals[key]);
    }
  });
  return returnMealDTOs;
});

function removeCombinedMealKey(keys: string[]) {
  let indexToRemove = -1;
  keys.forEach((mealId) => {
    if (
      parseInt(mealId) > 0 &&
      props.meals[mealId] !== undefined &&
      props.meals[mealId] !== null &&
      props.meals[mealId].length > 0 &&
      props.meals[mealId][0].dishSlug === 'combined-dish'
    ) {
      indexToRemove = keys.indexOf(mealId);
    }
  });
  if (indexToRemove !== -1) {
    keys.splice(indexToRemove, 1);
  }
}
</script>
