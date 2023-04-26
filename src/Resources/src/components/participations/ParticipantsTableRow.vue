<template>
  <tr class="table w-full table-fixed border-b-[1px] text-center last:border-b-0">
    <td
      class="w-1/2 truncate py-4 px-0"
    >
      {{ participantName }}
    </td>
    <td
      v-for="value in meals"
      :key="value.mealId"
      :class="value.title.en === 'Combined Dish' ? 'w-1/2' : 'w-full'"
      class="py-4 px-0"
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
  participantName: string,
  bookedMeals: IBookedData,
  meals: IMealWithVariations[]
}>();

const bookedCombinedMeal = computed(() => {
  for(const meal of props.meals) {
    if(meal.title.en === 'Combined Dish') {
      if(props.bookedMeals.booked.includes(meal.mealId)) {
        return true;
      }
    }
  }
  return false;
});
</script>