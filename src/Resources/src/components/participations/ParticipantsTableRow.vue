<template>
  <tr
    class="table w-full table-fixed border-b-[1px] text-center last:border-b-0"
    :class="{ 'bg-highlight': isOfferingMeal }"
  >
    <td class="w-2/3 truncate py-4 pl-4 pr-0 text-left font-bold tracking-wider">
      {{ participantName }}
    </td>
    <td
      v-for="value in meals"
      :key="value.mealId"
      :class="value.title.en === 'Combined Dish' ? 'w-1/3' : 'w-full'"
      class="px-0 py-4"
    >
      <ParticipantsTableData
        :booked-meals="bookedMeals"
        :meal="value"
        :booked-combined-meal="bookedCombinedMeal"
      />
    </td>
  </tr>
</template>

<script setup lang="ts">
import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';
import ParticipantsTableData from './ParticipantsTableData.vue';
import { computed } from 'vue';

const props = defineProps<{
  participantName: string;
  bookedMeals: IBookedData;
  meals: IMealWithVariations[];
}>();

const bookedCombinedMeal = computed(() => {
  for (const meal of props.meals) {
    if (meal.title.en === 'Combined Dish') {
      if (props.bookedMeals.booked.includes(meal.mealId)) {
        return true;
      }
    }
  }
  return false;
});

const isOfferingMeal = computed(() => {
  if (props.bookedMeals.isOffering !== null && props.bookedMeals.isOffering !== undefined) {
    return props.bookedMeals.isOffering.find(bool => bool === true);
  }
  return false;
});
</script>
