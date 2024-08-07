<template>
  <tr class="table w-full table-fixed border-b text-center last:border-b-0">
    <td
      class="w-2/3 truncate py-4 pl-4 pr-0 text-left font-bold tracking-wider"
      :class="{ 'border-l-4 border-l-highlight': isOfferingMeal }"
    >
      <div class="flex w-full flex-row">
        <span>
          {{ getDisplayName(participantName) }}
        </span>
        <svg
          v-if="isOfferingMeal"
          class="right-0 ml-auto mr-4 inline aspect-square h-full fill-primary-1"
          xmlns="http://www.w3.org/2000/svg"
          height="24"
          viewBox="0 -960 960 960"
          width="24"
        >
          <path
            d="m274-200 34 34q12 12 11.5 28T308-110q-12 12-28.5 12.5T251-109L148-212q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l103-103q12-12 28.5-11.5T308-370q11 12 11.5 28T308-314l-34 34h406v-120q0-17 11.5-28.5T720-440q17 0 28.5 11.5T760-400v120q0 33-23.5 56.5T680-200H274Zm412-480H280v120q0 17-11.5 28.5T240-520q-17 0-28.5-11.5T200-560v-120q0-33 23.5-56.5T280-760h406l-34-34q-12-12-11.5-28t11.5-28q12-12 28.5-12.5T709-851l103 103q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L709-589q-12 12-28.5 11.5T652-590q-11-12-11.5-28t11.5-28l34-34Z"
          />
        </svg>
      </div>
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
import type { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';
import ParticipantsTableData from './ParticipantsTableData.vue';
import { computed } from 'vue';
import getDisplayName from '@/services/useConvertDisplayName';

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
    return props.bookedMeals.isOffering.find((bool) => bool === true);
  }
  return false;
});
</script>
